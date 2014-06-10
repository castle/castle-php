<?php

class UserbinSessionTest extends Userbin_TestCase
{
  public static function setUpBeforeClass() {
    Userbin::setApiKey('secretkey');
  }

  public function tearDown()
  {
    Userbin_RequestTransport::reset();
  }

  public function exampleSession()
  {
    return [
      [array(
        'id' => 1,
        'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s',
        'user' => array(
          'id' => 1,
          'email' => 'hello@example.com'
        )
      )]
    ];
  }

  /**
   * @dataProvider exampleSession
   */
  public function testRefresh($sessionData)
  {
    Userbin_RequestTransport::setResponse(201, $sessionData);
    $session = new Userbin_Session($sessionData);
    $session->refresh();
    $this->assertRequest('post', '/sessions/'.$session->token.'/refresh');
  }

  /**
   * @dataProvider exampleSession
   */
  public function testHasExpired($session)
  {
    $session = new Userbin_Session($session);
    $this->assertTrue($session->hasExpired());
  }

  /**
   * @dataProvider exampleSession
   */
  public function testUpdateUserInfo($session)
  {
    $newSession = new Userbin_Session($session);
    $session['user']['email'] = 'new_email@example.com';
    Userbin_RequestTransport::setResponse(201, $session);
    $newSession->refresh($session['user']);
    $this->assertEquals($newSession->user['email'], 'new_email@example.com');
  }

  /**
   * @dataProvider exampleSession
   */
  public function testSerialize($sessionData)
  {
    $session = new Userbin_Session($sessionData);
    $token = $session->serialize();
  }

  /**
   * @dataProvider exampleSession
   */
  public function testLoad($sessionData)
  {
    $session = Userbin_Session::load($sessionData['token']);
    $user = $session->user();
    $this->assertTrue($user instanceof Userbin_User);
  }
}
?>