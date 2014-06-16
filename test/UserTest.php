<?php

class UserbinUserTest extends Userbin_TestCase
{
  public static function setUpBeforeClass()
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
  }

  public function tearDown()
  {
    Userbin_RequestTransport::reset();
  }

  public function exampleUser()
  {
    return [
      [array(
        'id' => 1,
        'email' => 'hello@example.com'
      )]
    ];
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreateChallenge($userData)
  {
    $user = new Userbin_User($userData);
    $user->challenges()->create();
    $this->assertRequest('post', '/users/'.$user->id.'/challenges');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreateSession($userData)
  {
    $user = new Userbin_User($userData);
    $user->sessions()->create();
    $this->assertRequest('post', '/users/'.$user->id.'/sessions');
  }

  /**
   * @dataProvider exampleUser
   * @expectedException Userbin_Error
   */
  public function testCreateSessionInvalidResponse($user)
  {
    Userbin_RequestTransport::setResponse(200, '');
    $user = new Userbin_User($user);
    $user->sessions()->create();
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreateSessionSendsUserdata($userData)
  {
    Userbin_RequestTransport::setResponse(200, array());
    $user = new Userbin_User($userData);
    $user->sessions()->create(array('user' => $userData));
    $request = Userbin_RequestTransport::getLastRequest();
    $this->assertRequest('post', '/users/'.$user->id.'/sessions');
    $this->assertEquals($request['params']['user'], $userData);
  }
}
