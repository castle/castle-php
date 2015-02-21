<?php

class CastleUserTest extends Castle_TestCase
{
  public static function setUpBeforeClass()
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
  }

  public function tearDown()
  {
    Castle_RequestTransport::reset();
  }

  public function exampleUser()
  {
    return array(
      array(array(
        'id' => 1,
        'email' => 'hello@example.com'
      ))
    );
  }

  /**
   * @dataProvider exampleUser
   */
  public function testBackupCodes($userData)
  {
    $user = new Castle_User($userData);
    $user->backupCodes()->generate();
    $this->assertRequest('post', '/users/'.$user->id.'/backup_codes');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreateChallenge($userData)
  {
    $user = new Castle_User($userData);
    $user->challenges()->create();
    $this->assertRequest('post', '/users/'.$user->id.'/challenges');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreateSession($userData)
  {
    $user = new Castle_User($userData);
    $user->sessions()->create();
    $this->assertRequest('post', '/users/'.$user->id.'/sessions');
  }

  /**
   * @dataProvider exampleUser
   * @expectedException Castle_Error
   */
  public function testCreateSessionInvalidResponse($user)
  {
    Castle_RequestTransport::setResponse(200, '');
    $user = new Castle_User($user);
    $user->sessions()->create();
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreateSessionSendsUserdata($userData)
  {
    Castle_RequestTransport::setResponse(200, array());
    $user = new Castle_User($userData);
    $user->sessions()->create(array('user' => $userData));
    $request = $this->assertRequest('post', '/users/'.$user->id.'/sessions');
    $this->assertEquals($request['params']['user'], $userData);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testDisableMFA($userData)
  {
    $user = new Castle_User($userData);
    $user->disableMFA();
    $this->assertRequest('post', '/users/'.$user->id.'/disable_mfa');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testEnableMFA($userData)
  {
    $user = new Castle_User($userData);
    $user->enableMFA();
    $this->assertRequest('post', '/users/'.$user->id.'/enable_mfa');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testEventsMFA($userData)
  {
    $user = new Castle_User($userData);
    $user->events()->fetch();
    $this->assertRequest('get', '/users/'.$user->id.'/events');
  }
}
