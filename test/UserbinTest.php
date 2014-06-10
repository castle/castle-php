<?php

class UserbinTest extends Userbin_TestCase
{
  public static function setUpBeforeClass() {
    Userbin::setApiKey('secretkey');
    $_SESSION = array();
  }

  public function testSetApiKey()
  {
    $this->assertContains('secretkey', Userbin::getApiKey());
  }

  public function exampleUser()
  {
    return [
      [array('id' => 1, 'email' => 'hello@example.com')]
    ];
  }

  public function exampleSessionToken()
  {
    return[['eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s']];
  }

  /**
   * @dataProvider exampleUser
   */
  public function testStartSessionWithoutExistingSession($user)
  {
    Userbin_RequestTransport::setResponse(201, array('token' => '1234'));
    $session = Userbin::startSession($user['id'], $user);
    $this->assertEquals($session->token, '1234');
    $this->assertRequest('post', '/users/'.$user['id'].'/sessions');
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testStartSessionWithExistingSession($token)
  {
    Userbin_RequestTransport::setResponse(201, array('token' => $token));
    $_SESSION['userbin'] = $token;
    $session = Userbin::startSession(1);
    $this->assertEquals($session->token, $token);
    $this->assertRequest('post', '/sessions/'.$token.'/refresh');
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testDestroySession($token)
  {
    $_SESSION['userbin'] = $token;
    Userbin::destroySession();
    $this->assertFalse(array_key_exists('userbin', $_SESSION));
    $this->assertRequest('delete', '/sessions/'.$token);
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