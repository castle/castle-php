<?php
class CastleModelTest extends Castle_TestCase
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

  public function snakeCases() {
    return array(
      array('simpleTest', 'simple_test'),
      array('easy', 'easy'),
      array('HTML', 'html'),
      array('simpleXML', 'simple_xml'),
      array('PDFLoad', 'pdf_load'),
      array('startMIDDLELast', 'start_middle_last'),
      array('AString', 'a_string'),
      array('Some4Numbers234', 'some4_numbers234'),
      array('TEST123String', 'test123_string')
    );
  }

  public function testSetAttributesInConstructor()
  {
    $attributes = array(
      'id' => 1,
      'email' => 'hello@example.com'
    );
    $model = new RestModel($attributes);

    $this->assertEquals($model->email, $attributes['email']);
    $this->assertEquals($model->id, $attributes['id']);
  }

  /**
   * @dataProvider snakeCases
   */
  public function testSnakeCase($camel, $snake)
  {
    $this->assertEquals(RestModel::snakeCase($camel), $snake);
  }

  public function testGetName()
  {
    $user = new Castle_User();
    $this->assertEquals($user->getResourceName(), 'users');
  }

  public function testGetResourcePathWithoutId()
  {
    $user = new Castle_User();
    $this->assertEquals($user->getResourcePath(), '/users');
  }

  public function testGetResourcePathWithId()
  {
    $userData = array('id' => 1);
    $user = new Castle_User($userData);
    $this->assertEquals($user->getResourcePath(), '/users/1');
  }

  // public function testConstructorSetsReference()
  // {
  //   $attributes = array('id' => 1);
  //   $model = new Castle_Model($attributes);
  //   $attributes['id'] = 2;
  //   $this->assertEquals($model->id, $attributes['id']);
  // }

  /**
   * @dataProvider exampleUser
   */
  public function testCreate($user)
  {
    Castle_RequestTransport::setResponse(200, $user);
    $user = Castle_User::create(array('email' => 'hello@example.com'));
    $this->assertRequest('post', '/users');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testAll($user)
  {
    Castle_RequestTransport::setResponse(200, array($user, $user));
    $users = Castle_User::all();
    $this->assertRequest('get', '/users');
    $this->assertEquals($users[0]->id, $user['id']);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreateSendsParams($user)
  {
    Castle_User::create($user);
    $request = Castle_RequestTransport::getLastRequest();
    $this->assertEquals($user, $request['params']);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testDestroy($user) {
    Castle_RequestTransport::setResponse(204);
    Castle_User::destroy($user['id']);
    $this->assertRequest('delete', '/users/'.$user['id']);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testFind($user)
  {
    Castle_RequestTransport::setResponse(201, $user);
    $found_user = Castle_User::find($user['id']);
    $this->assertRequest('get', '/users/'.$user['id']);
    $this->assertEquals($found_user->email, $user['email']);
  }

  public function testInstancePost()
  {
    Castle_RequestTransport::setResponse(201, array('id' => '1', 'verified' => true));
    $challenge = new Castle_Challenge(1);
    $response = $challenge->verify('12345');
    $this->assertEquals(1, $challenge->id);
    $this->assertEquals(true, $challenge->verified);
    $this->assertInstanceOf('Castle_Challenge', $response);
  }

  public function testNestedFind()
  {
    $user = new Castle_User(1234);
    $user->challenges()->find(5678);
    $this->assertRequest('get', '/users/1234/challenges/5678');
  }

  public function testNestedInstanceMethod()
  {
    Castle_RequestTransport::setResponse(200, array('id' => 1));
    $user = new Castle_User(1234);
    $challenge = $user->challenges()->find(1);
    $challenge->verify('response');
    $this->assertRequest('post', '/users/1234/challenges/1/verify');
  }

  public function testBelongsToWithIdAttribute()
  {
    $challenge = new Castle_Challenge(array('id' => 1, 'pairing_id' => 2));
    $pairing = $challenge->pairing();
    $this->assertInstanceOf('Castle_Pairing', $pairing);
    $this->assertEquals(2, $pairing->id);
  }

  public function testBelongsToWithObject()
  {
    $challenge = new Castle_Challenge(array('id' => 1, 'pairing' => array('id' => 2)));
    $pairing = $challenge->pairing();
    $this->assertInstanceOf('Castle_Pairing', $pairing);
    $this->assertEquals(2, $pairing->id);
  }

  public function testBelongsToWithoutId()
  {
    $challenge = new Castle_Challenge(array('id' => 1));
    $pairing = $challenge->pairing();
    $this->assertNull($pairing);
  }

  public function testHasOne()
  {
    $userData = array(
      'id' => 1,
      'session' => array('id' => 1)
    );
    $user = new Castle_User($userData);
    $session = $user->hasOne('Castle_Session');
    $this->assertEquals(1, $session->id);
    $session->save();
    $this->assertRequest('put', '/users/1/session');
  }

  public function testHasManyForSingleResourceInstanceMethod()
  {
    $user = new Castle_User(1);
    $challenge = $user->challenges()->verify(1, 'response');
    $this->assertRequest('post', '/users/1/challenges/1/verify');
  }


  public function testEscapeUrl()
  {
    $user = new Castle_User('Hofbräuhaus / München');
    $user->fetch();
    $request = Castle_RequestTransport::getLastRequest();
    $this->assertStringEndsWith('Hofbr%C3%A4uhaus%20%2F%20M%C3%BCnchen', $request['url']);
  }
}
