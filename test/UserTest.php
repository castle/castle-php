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
  public function testEvents($userData)
  {
    $user = new Castle_User($userData);
    $user->events()->fetch();
    $this->assertRequest('get', '/users/'.$user->id.'/events');
  }
}
