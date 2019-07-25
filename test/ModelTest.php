<?php
class TestModel extends RestModel {
  protected $stripPrefix = "test_";
}

class TestUser extends TestModel
{
  public function shoes()
  {
    return $this->hasMany('TestShoe');
  }
}

class TestShoe extends TestModel
{
  public function user()
  {
    return $this->belongsTo('TestUser');
  }

  public function walk($response)
  {
    return $this->post('walk', array('response' => $response));
  }
}

class CastleModelTest extends Castle_TestCase
{
  public static function setUpBeforeClass(): void
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
  }

  public function tearDown(): void
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
    $user = new TestUser();
    $this->assertEquals($user->getResourceName(), 'users');
  }

  public function testGetResourcePathWithoutId()
  {
    $user = new TestUser();
    $this->assertEquals($user->getResourcePath(), '/users');
  }

  public function testGetResourcePathWithId()
  {
    $userData = array('id' => 1);
    $user = new TestUser($userData);
    $this->assertEquals($user->getResourcePath(), '/users/1');
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreate($user)
  {
    Castle_RequestTransport::setResponse(200, $user);
    $user = TestUser::create(array('email' => 'hello@example.com'));
    $this->assertRequest('post', '/users');
  }

  public function testCreateWithEmptyResponse() {
    Castle_RequestTransport::setResponse(204, null);
    TestUser::create();
    $this->assertEquals(true, true);

  }

  /**
   * @dataProvider exampleUser
   */
  public function testAll($user)
  {
    Castle_RequestTransport::setResponse(200, array($user, $user));
    $users = TestUser::all();
    $this->assertRequest('get', '/users');
    $this->assertEquals($users[0]->id, $user['id']);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreateSendsParams($user)
  {
    TestUser::create($user);
    $request = Castle_RequestTransport::getLastRequest();
    $this->assertEquals($user, $request['params']);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testDestroy($user) {
    Castle_RequestTransport::setResponse(204);
    TestUser::destroy($user['id']);
    $this->assertRequest('delete', '/users/'.$user['id']);
  }

  /**
   * @dataProvider exampleUser
   */
  public function testFind($user)
  {
    Castle_RequestTransport::setResponse(201, $user);
    $found_user = TestUser::find($user['id']);
    $this->assertRequest('get', '/users/'.$user['id']);
    $this->assertEquals($found_user->email, $user['email']);
  }

  public function testInstancePost()
  {
    Castle_RequestTransport::setResponse(201, array('id' => '1', 'walked' => true));
    $shoe = new TestShoe(1);
    $response = $shoe->walk('12345');
    $this->assertEquals(1, $shoe->id);
    $this->assertEquals(true, $shoe->walked);
    $this->assertInstanceOf('TestShoe', $response);
  }

  public function testNestedFind()
  {
    $user = new TestUser(1234);
    $user->shoes()->find(5678);
    $this->assertRequest('get', '/users/1234/shoes/5678');
  }

  public function testNestedInstanceMethod()
  {
    Castle_RequestTransport::setResponse(200, array('id' => 1));
    $user = new TestUser(1234);
    $shoe = $user->shoes()->find(1);
    $shoe->walk('response');
    $this->assertRequest('post', '/users/1234/shoes/1/walk');
  }

  public function testBelongsToWithIdAttribute()
  {
    $house = new TestShoe(array('id' => 1, 'user_id' => 2));
    $user = $house->user();
    $this->assertInstanceOf('TestUser', $user);
    $this->assertEquals(2, $user->id);
  }

  public function testBelongsToWithObject()
  {
    $house = new TestShoe(array('id' => 1, 'user' => array('id' => 2)));
    $user = $house->user();
    $this->assertInstanceOf('TestUser', $user);
    $this->assertEquals(2, $user->id);
  }

  public function testBelongsToWithoutId()
  {
    $house = new TestShoe(array('id' => 1));
    $user = $house->user();
    $this->assertNull($user);
  }

  public function testHasOne()
  {
    $userData = array(
      'id' => 1,
      'shoe' => array('id' => 1)
    );
    $user = new TestUser($userData);
    $shoe = $user->hasOne('TestShoe');
    $this->assertEquals(1, $shoe->id);
    $shoe->save();
    $this->assertRequest('put', '/users/1/shoe');
  }

  public function testHasManyForSingleResourceInstanceMethod()
  {
    $user = new TestUser(1);
    $user->shoes()->walk(1, 'response');
    $this->assertRequest('post', '/users/1/shoes/1/walk');
  }


  public function testEscapeUrl()
  {
    $user = new TestUser('Hofbräuhaus / München');
    $user->fetch();
    $request = Castle_RequestTransport::getLastRequest();
    $this->assertStringEndsWith('Hofbr%C3%A4uhaus%20%2F%20M%C3%BCnchen', $request['url']);
  }
}
