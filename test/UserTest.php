<?php

class UserbinUserTest extends Userbin_TestCase
{
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

  public function testGetName()
  {
    $user = new Userbin_User();
    $this->assertEquals($user->getResourceName(), 'users');
  }

  public function testGetRequestPathWithoutId()
  {
    $user = new Userbin_User();
    $this->assertEquals($user->getRequestPath(), '/users');
  }

  public function testGetRequestPathWithId()
  {
    $user = new Userbin_User(array('id' => 1));
    $this->assertEquals($user->getRequestPath(), '/users/1');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreateSession($user)
  {
    $user = new Userbin_User($user);
    $user->createSession();
    $this->assertRequest('post', '/users/'.$user->id.'/sessions');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreate($user)
  {
    Userbin_RequestTransport::setResponse(200, $user);
    $user = Userbin_User::create($user);
    $this->assertRequest('post', '/users');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testDestroy($user) {
    Userbin_RequestTransport::setResponse(204);
    Userbin_User::destroy($user['id']);
    $this->assertRequest('delete', '/users/'.$user['id']);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testFind($user)
  {
    Userbin_RequestTransport::setResponse(201, $user);
    $found_user = Userbin_User::find($user['id']);
    $this->assertRequest('get', '/users/'.$user['id']);
    $this->assertEquals($found_user->email, $user['email']);
  }
}

?>


