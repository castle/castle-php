<?php
class UserbinModelTest extends Userbin_TestCase
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

  public function snakeCases() {
    return array(
      ['simpleTest', 'simple_test'],
      ['easy', 'easy'],
      ['HTML', 'html'],
      ['simpleXML', 'simple_xml'],
      ['PDFLoad', 'pdf_load'],
      ['startMIDDLELast', 'start_middle_last'],
      ['AString', 'a_string'],
      ['Some4Numbers234', 'some4_numbers234'],
      ['TEST123String', 'test123_string']
    );
  }

  public function testSetAttributesInConstructor()
  {
    $attributes = array(
      'id' => 1,
      'email' => 'hello@example.com'
    );
    $model = new Userbin_Model($attributes);

    $this->assertEquals($model->email, $attributes['email']);
    $this->assertEquals($model->id, $attributes['id']);
  }

  /**
   * @dataProvider snakeCases
   */
  public function testSnakeCase($camel, $snake)
  {
    $this->assertEquals(Userbin_Model::snakeCase($camel), $snake);
  }

  public function testGetName()
  {
    $user = new Userbin_User();
    $this->assertEquals($user->getResourceName(), 'users');
  }

  public function testGetResourcePathWithoutId()
  {
    $user = new Userbin_User();
    $this->assertEquals($user->getResourcePath(), '/users');
  }

  public function testGetResourcePathWithId()
  {
    $user = new Userbin_User(array('id' => 1));
    $this->assertEquals($user->getResourcePath(), '/users/1');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreate($user)
  {
    Userbin_RequestTransport::setResponse(200, $user);
    $user = Userbin_User::create(array('email' => 'hello@example.com'));
    $this->assertRequest('post', '/users');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testAll($user)
  {
    Userbin_RequestTransport::setResponse(200, array($user, $user));
    $users = Userbin_User::all();
    $this->assertRequest('get', '/users');
    $this->assertEquals($users[0]->id, $user['id']);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreateSendsParams($user)
  {
    Userbin_User::create($user);
    $request = Userbin_RequestTransport::getLastRequest();
    $this->assertEquals($user, $request['params']);
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

  public function testHasOne()
  {
    $userData = array(
      'id' => 1,
      'session' => array('token' => 1)
    );
    $user = new Userbin_User($userData);
    $session = $user->hasOne('Userbin_Session', $user->session);
    $session->save();
    $this->assertRequest('put', '/users/1/session');
  }
}

?>