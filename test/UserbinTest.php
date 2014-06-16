<?php

class UserbinTest extends Userbin_TestCase
{
  protected $sessionToken;

  public static function setUpBeforeClass() {
    Userbin::setApiKey('secretkey');
  }

  protected function setUp()
  {
    $this->sessionToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s';
    $_SESSION = array();
  }

  public function testSetApiKey()
  {
    $this->assertContains('secretkey', Userbin::getApiKey());
  }

  public function exampleUser()
  {
    return [
      [array('id' => 'user-2412', 'email' => 'hello@example.com')]
    ];
  }

  public function exampleSessionToken()
  {
    return[['eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s']];
  }

  public function exampleSessionTokenWithMFA()
  {
    $jwt = new Userbin_JWT();
    $jwt->setHeader(array('vfy' => '1', 'iss' => '1'));
    return [[$jwt->toString()]];
  }

  public function exampleSessionTokenWithChallenge()
  {
    $jwt = new Userbin_JWT();
    $jwt->setHeader(array('vfy' => '1', 'iss' => '1'));
    $jwt->setBody('chg', '1');
    return [[$jwt->toString()]];
  }

  /**
   * @dataProvider exampleUser
   */
  public function testAuthorizeWithoutExistingSession($userData)
  {
    Userbin_RequestTransport::setResponse(201, array('token' => $this->sessionToken));

    $user = Userbin::authorize($userData['id'], $userData);
    $this->assertEquals($user->id, $userData['id']);
    $this->assertRequest('post', '/users/'.$userData['id'].'/sessions');
    $this->assertTrue(Userbin::getSession() instanceof Userbin_Session);
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testAuthorizeWithExistingSession($token)
  {
    Userbin_RequestTransport::setResponse(201, array('token' => $token));
    $_SESSION['userbin'] = $token;
    $user = Userbin::authorize('user-2412');
    $this->assertEquals($user->id, 'user-2412');
    $this->assertRequest('post', '/synchronize', array('X-Userbin-Session-Token' => $token));
  }

  /**
   * @dataProvider exampleSessionToken
   * @expectedException Userbin_Error
   */
  public function testAuthorizeWithExistingSessionAndWrongUser($token)
  {
    $_SESSION['userbin'] = $token;
    $user = Userbin::authorize('wrong-user-id');
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testLogout($token)
  {
    Userbin::getSessionStore()->write($token);
    $session = Userbin::getSession();
    Userbin::logout();
    $this->assertRequest('delete', '/sessions/'.$session->getId());
    $this->assertFalse(array_key_exists('userbin', $_SESSION));
  }

  /**
   * @dataProvider exampleSessionTokenWithMFA
   */
  public function testTwoFactorAuthenticate($sessionToken)
  {
    Userbin_RequestTransport::setResponse(201, array('id' => '1'));
    Userbin::getSessionStore()->write($sessionToken);
    Userbin::twoFactorAuthenticate();
    $session = Userbin::getSession();
    $this->assertInstanceOf('Userbin_Challenge', $session->getChallenge());
  }

  /**
   * @dataProvider exampleSessionTokenWithChallenge
   */
  public function testTwoFactorVerify($sessionToken)
  {
    Userbin_RequestTransport::setResponse(200);
    Userbin::getSessionStore()->write($sessionToken);
    $this->assertTrue(Userbin::twoFactorVerify('1234'));
    $session = Userbin::getSession();
    $this->assertNull($session->getChallenge());
  }



  /**
   * @dataProvider exampleSessionToken
   */
  public function testSecuritySettingsUrl($token)
  {
    $_SESSION['userbin'] = $token;
    $url = Userbin::securitySettingsUrl();
    $this->assertContains($token, $url);
  }

  /**
   * @expectedException Userbin_Error
   */
  public function testSecuritySettingsUrlWithoutSession()
  {
    $url = Userbin::securitySettingsUrl();
    $this->assertEmpty($url);
  }
}

?>