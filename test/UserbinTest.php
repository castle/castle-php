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
   */
  public function testLogout($token)
  {
    $_SESSION['userbin'] = $token;
    $session = Userbin::getSession();
    Userbin::logout();
    $this->assertRequest('delete', '/sessions/'.$session->getId());
    $this->assertFalse(array_key_exists('userbin', $_SESSION));
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