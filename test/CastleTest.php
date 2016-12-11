<?php

class CastleTest extends Castle_TestCase
{
  protected $sessionToken;

  public static function setUpBeforeClass() {
    Castle::setApiKey('secretkey');
  }

  public function setUp()
  {
    $_SESSION = array();
    $_COOKIE = array();
  }

  public function testSetApiKey()
  {
    $this->assertContains('secretkey', Castle::getApiKey());
  }

  public function testTrack()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::track(array('name' => '$login.failed'));
    $this->assertRequest('post', '/events');
  }

  public function testAuthenticate()
  {
    Castle_RequestTransport::setResponse(201, '{ "status": "approve" }');
    $auth = Castle::authenticate(Array(
      'user_id' => $user_id,
      'name' => '$login.failed'
    ));
    $this->assertRequest('post', '/authenticate');
    $this->assertEquals($auth->status, 'approve');
  }
}
