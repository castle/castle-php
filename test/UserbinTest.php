<?php

class UserbinTest extends Userbin_TestCase
{
  protected $sessionToken;

  public static function setUpBeforeClass() {
    Userbin::setApiKey('secretkey');
  }

  public function setUp()
  {
    $this->sessionToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s';
    $_SESSION = array();
    $_COOKIE = array();
  }

  public function testSetApiKey()
  {
    $this->assertContains('secretkey', Userbin::getApiKey());
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
    $jwt = new Userbin_JWT();
    $jwt->setHeader(array('iss' => '1', 'exp' => time()));
    $jwt->setBody('vfy', 1);
    return array(array($jwt->toString()));
  }

  public function exampleSessionTokenWithChallenge()
  {
    $jwt = new Userbin_JWT();
    $jwt->setHeader(array('iss' => '1', 'exp' => time()));
    $jwt->setBody('chg', 1);
    $jwt->setBody('dpr', 1);
    $jwt->setBody('mfa', 1);
    $jwt->setBody('vfy', 1);
    $jwt->setBody('typ', 'authenticator');
    return array(array($jwt->toString()));
  }

  /**
   * @dataProvider exampleUser
   */
  public function testLoginWithoutExistingSession($userData)
  {
    Userbin_RequestTransport::setResponse(201, array('token' => $this->sessionToken));

    $session = Userbin::login($userData['id'], $userData);
    $this->assertEquals($this->sessionToken, $session->serialize());
    $this->assertRequest('post', '/users/'.$userData['id'].'/sessions');
    $this->assertInstanceOf('Userbin_SessionToken', $session);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testLoginSendsUserData($userData)
  {
    $user = Userbin::login($userData['id'], $userData);
    $request = $this->assertRequest('post', '/users/'.$userData['id'].'/sessions');
    $this->assertEquals($request['params']['user'], $userData);
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testLoginWithTrustedDevice($token)
  {
    Userbin::setSessionToken($token);
    Userbin_RequestTransport::setResponse(201, array('token' => '12345'));
    Userbin::trustDevice();
    Userbin_RequestTransport::setResponse(201, array('token' => $token));
    Userbin::login(1);
    $request = $this->assertRequest('post', '/users/1/sessions');
    $this->assertEquals('12345', $request['params']['trusted_device_token']);
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testAuthorizeWithExistingSession($token)
  {
    Userbin::setSessionToken($token);
    Userbin::authorize();
    $this->assertRequest('post', '/heartbeat', array('X-Userbin-Session-Token' => $token));
  }


  /**
   * @expectedException Userbin_UserUnauthorizedError
   */
  public function testAuthorizeWithoutSession()
  {
    Userbin::authorize();
  }

  /**
   * @dataProvider exampleSessionTokenWithMFA
   * @expectedException Userbin_ChallengeRequiredError
   */
  public function testAuthorizeWithMFASession($token)
  {
    Userbin::setSessionToken($token);
    Userbin::authorize();
  }

  /**
   * @dataProvider exampleSessionTokenWithChallenge
   * @expectedException Userbin_UserUnauthorizedError
   */
  public function testAuthorizeWithChallengeSession($token)
  {
    Userbin::setSessionToken($token);
    Userbin::authorize();
  }

  /**
   * @dataProvider exampleSessionTokenWithChallenge
   */
  public function testHasDefaultPairing($token)
  {
    $this->assertFalse(Userbin::hasDefaultPairing());
    Userbin::setSessionToken($token);
    $this->assertTrue(Userbin::hasDefaultPairing());
  }

  /**
   * @dataProvider exampleSessionTokenWithChallenge
   */
  public function testIsMFAEnabled($token)
  {
    $this->assertFalse(Userbin::isMFAEnabled());
    Userbin::setSessionToken($token);
    $this->assertTrue(Userbin::isMFAEnabled());
  }


  /**
   * @dataProvider exampleSessionToken
   */
  public function testLogout($token)
  {
    Userbin::getTokenStore()->setSession($token);
    $session = Userbin::getSessionToken();
    Userbin::logout();
    $this->assertRequest('delete', '/users/%24current/sessions/'.$session->getId());
    $this->assertFalse(array_key_exists('userbin', $_SESSION));
  }

  public function testLogoutWithoutToken()
  {
    $this->assertFalse(Userbin::logout());
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testLogoutWithNonExisting($token)
  {
    Userbin_RequestTransport::setResponse(404);
    Userbin::getTokenStore()->setSession($token);
    $session = Userbin::getSessionToken();
    Userbin::logout();
    /* No exception should be thrown */
  }


  /**
   * @dataProvider exampleSessionToken
   */
  public function testIsAuthorized($token)
  {
    $this->assertFalse(Userbin::isAuthorized());
    Userbin::setSessionToken($token);
    $this->assertTrue(Userbin::isAuthorized());
  }

  /**
   * @expectedException Userbin_UserUnauthorizedError
   */
  public function testTrustDeviceWithoutUser()
  {
    Userbin::trustDevice();
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testTrustDeviceWithSession($token)
  {
    Userbin_RequestTransport::setResponse(201, array('token' => '12345'));
    Userbin::getTokenStore()->setSession($token);
    Userbin::trustDevice();
    $this->assertEquals('12345', Userbin::trustedDeviceToken());
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testCurrentUserVerifyPairing($token)
  {
    Userbin::getTokenStore()->setSession($token);
    Userbin_RequestTransport::setResponse(201, array('id' => '12345', 'verified' => true));
    $pairing = Userbin::currentUser()->pairings()->verify(1, array('response' => '12345'));
    $this->assertInstanceOf('Userbin_Pairing', $pairing);
  }
}
