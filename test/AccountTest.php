<?php

class CastleAccountTest extends Castle_TestCase
{
  public static function setUpBeforeClass() {
    Castle::setApiKey('secretkey');
    $_SERVER = array();
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
  }

  public function tearDown()
  {
    Castle_RequestTransport::reset();
  }

  public function exampleSettings()
  {
    return array(array(array(
      'app_id' => 1234,
      'api_secret' => 'secret',
      'settings' => array(
        'public_name' => 'Test'
      )
    )));
  }

  /**
   * @dataProvider exampleSettings
   */
  public function testAccountGetRequest($settings)
  {
    Castle_RequestTransport::setResponse(200, $settings);
    $settings = new Castle_Account();
    $settings->fetch();
    $this->assertRequest('get', '/account');
  }

  /**
   * @dataProvider exampleSettings
   */
  public function testAccountPostRequest($settings)
  {
    Castle_RequestTransport::setResponse(200, $settings);
    $settings = new Castle_Account($settings);
    $settings->public_name = 'New name';
    $settings->save();
    $request = $this->assertRequest('post', '/account');
    $this->assertArrayHasKey('settings', $request['params']);
    $this->assertEquals('New name', $request['params']['settings']['public_name']);
  }

  /**
   * @dataProvider exampleSettings
   */
  public function testGetter($settings)
  {
    $settings = new Castle_Account($settings);
    $this->assertEquals('Test', $settings->public_name);
    $this->assertNull($settings->nothing);
  }

  public function testSetter()
  {
    $settings = new Castle_Account();
    $settings->public_name = "Test";
    $this->assertArrayHasKey('settings', $settings->getAttributes());
    $this->assertEquals('Test', $settings->public_name);
  }
}
