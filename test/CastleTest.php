<?php

class CastleTest extends Castle_TestCase
{
  protected $sessionToken;

  public static function setUpBeforeClass() {
    Castle::setApiKey('secretkey');
  }

  public function setUp()
  {
    $this->sessionToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s';
    $_SESSION = array();
    $_COOKIE = array();
  }

  public function testSetApiKey()
  {
    $this->assertContains('secretkey', Castle::getApiKey());
  }

  public function exampleUser()
  {
    return array(
      array(array('id' => 'user-2412', 'email' => 'hello@example.com'))
    );
  }

  public function exampleSessionToken()
  {
    return array(array('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s'));
  }

  public function exampleSessionTokenWithMFA()
  {
    Castle::setApiKey('secretkey');
    $jwt = new Castle_JWT();
    $jwt->setHeader(array('iss' => '1', 'exp' => time()));
    $jwt->setBody('vfy', 1);
    return array(array($jwt->toString()));
  }

  public function exampleSessionTokenWithChallenge()
  {
    Castle::setApiKey('secretkey');
    $jwt = new Castle_JWT();
    $jwt->setHeader(array('iss' => '1', 'exp' => time()));
    $jwt->setBody('chg', 1);
    $jwt->setBody('dpr', 1);
    $jwt->setBody('mfa', 1);
    $jwt->setBody('vfy', 1);
    $jwt->setBody('typ', 'authenticator');
    $jwt->isValid();
    return array(array($jwt->toString()));
  }

  /**
   * @dataProvider exampleUser
   */
  public function testLoginWithoutExistingSession($userData)
  {
    Castle_RequestTransport::setResponse(201, array('token' => $this->sessionToken));

    $session = Castle::login($userData['id'], $userData);
    $this->assertEquals($this->sessionToken, $session->serialize());
    $this->assertRequest('post', '/users/'.$userData['id'].'/sessions');
    $this->assertInstanceOf('Castle_SessionToken', $session);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testLoginSendsUserData($userData)
  {
    $user = Castle::login($userData['id'], $userData);
    $request = $this->assertRequest('post', '/users/'.$userData['id'].'/sessions');
    $this->assertEquals($request['params']['user'], $userData);
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testLoginWithTrustedDevice($token)
  {
    Castle::setSessionToken($token);
    Castle_RequestTransport::setResponse(201, array('token' => '12345'));
    Castle::trustDevice();
    Castle_RequestTransport::setResponse(201, array('token' => $token));
    Castle::login(1);
    $request = $this->assertRequest('post', '/users/1/sessions');
    $this->assertEquals('12345', $request['params']['trusted_device_token']);
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testAuthorizeWithExistingSession($token)
  {
    Castle::setSessionToken($token);
    Castle::authorize();
    $this->assertRequest('post', '/heartbeat', array('X-Castle-Session-Token' => $token));
  }


  /**
   * @expectedException Castle_UserUnauthorizedError
   */
  public function testAuthorizeWithoutSession()
  {
    Castle::authorize();
  }

  /**
   * @dataProvider exampleSessionTokenWithMFA
   * @expectedException Castle_ChallengeRequiredError
   */
  public function testAuthorizeWithMFASession($token)
  {
    Castle::setSessionToken($token);
    Castle::authorize();
  }

  /**
   * @dataProvider exampleSessionTokenWithChallenge
   * @expectedException Castle_UserUnauthorizedError
   */
  public function testAuthorizeWithChallengeSession($token)
  {
    Castle::setSessionToken($token);
    Castle::authorize();
  }

  /**
   * @dataProvider exampleSessionTokenWithChallenge
   */
  public function testHasDefaultPairing($token)
  {
    $this->assertFalse(Castle::hasDefaultPairing());
    Castle::setSessionToken($token);
    $this->assertTrue(Castle::hasDefaultPairing());
  }

  /**
   * @dataProvider exampleSessionTokenWithChallenge
   */
  public function testIsMFAEnabled($token)
  {
    $this->assertFalse(Castle::isMFAEnabled());
    Castle::setSessionToken($token);
    $this->assertTrue(Castle::isMFAEnabled());
  }


  /**
   * @dataProvider exampleSessionToken
   */
  public function testLogout($token)
  {
    Castle::getTokenStore()->setSession($token);
    $session = Castle::getSessionToken();
    Castle::logout();
    $this->assertRequest('delete', '/users/%24current/sessions/'.$session->getId());
    $this->assertFalse(array_key_exists('castle', $_SESSION));
  }

  public function testLogoutWithoutToken()
  {
    $this->assertFalse(Castle::logout());
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testLogoutWithNonExisting($token)
  {
    Castle_RequestTransport::setResponse(404);
    Castle::getTokenStore()->setSession($token);
    $session = Castle::getSessionToken();
    Castle::logout();
    /* No exception should be thrown */
  }


  /**
   * @dataProvider exampleSessionToken
   */
  public function testIsAuthorized($token)
  {
    $this->assertFalse(Castle::isAuthorized());
    Castle::setSessionToken($token);
    $this->assertTrue(Castle::isAuthorized());
  }

  public function testTrack()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::track(array('name' => '$login.failed'));
    $this->assertRequest('post', '/events');
  }

  /**
   * @expectedException Castle_UserUnauthorizedError
   */
  public function testTrustDeviceWithoutUser()
  {
    Castle::trustDevice();
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testTrustDeviceWithSession($token)
  {
    Castle_RequestTransport::setResponse(201, array('token' => '12345'));
    Castle::getTokenStore()->setSession($token);
    Castle::trustDevice();
    $this->assertEquals('12345', Castle::trustedDeviceToken());
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testCurrentUserVerifyPairing($token)
  {
    Castle::getTokenStore()->setSession($token);
    Castle_RequestTransport::setResponse(201, array('id' => '12345', 'verified' => true));
    $pairing = Castle::currentUser()->pairings()->verify(1, array('response' => '12345'));
    $this->assertInstanceOf('Castle_Pairing', $pairing);
  }
}
