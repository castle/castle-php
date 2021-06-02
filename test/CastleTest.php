<?php

class CastleTest extends Castle_TestCase
{
  protected $sessionToken;

  public static function setUpBeforeClass(): void {
    Castle::setApiKey('secretkey');
  }

  public function setUp(): void
  {
    $_SESSION = array();
    $_COOKIE = array();
  }

  public function testSetApiKey()
  {
    $this->assertEquals('secretkey', Castle::getApiKey());
  }

  public function testTrack()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::track(array('event' => '$login.failed'));
    $this->assertRequest('post', '/track');
  }

  public function testFilter()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::filter(Array(
      'request_token' => '7e51335b-f4bc-4bc7-875d-b713fb61eb23-bf021a3022a1a302',
      'name' => '$registration',
      'user' => Array('id' => 'abc', 'email' => 'user@foobar.io')
    ));
    $this->assertRequest('post', '/filter');
  }

  public function testLog()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::log(Array(
      'request_token' => '7e51335b-f4bc-4bc7-875d-b713fb61eb23-bf021a3022a1a302',
      'name' => '$login',
      'status' => '$failed',
      'user' => Array('id' => 'abc', 'email' => 'user@foobar.io')
    ));
    $this->assertRequest('post', '/log');
  }

  public function testRisk()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::risk(Array(
      'request_token' => '7e51335b-f4bc-4bc7-875d-b713fb61eb23-bf021a3022a1a302',
      'name' => '$login',
      'status' => '$succeeded',
      'user' => Array('id' => 'abc', 'email' => 'user@foobar.io')
    ));
    $this->assertRequest('post', '/risk');
  }

  public function testAuthenticate()
  {
    Castle_RequestTransport::setResponse(201, '{ "status": "approve" }');
    $auth = Castle::authenticate(Array(
      'user_id' => '1',
      'event' => '$login.failed'
    ));
    $this->assertRequest('post', '/authenticate');
    $this->assertEquals($auth->status, 'approve');
  }

  public function testImpersonate()
  {
      Castle_RequestTransport::setResponse(204, '');
      Castle::impersonate(array('user_id' => '1'));
      $this->assertRequest('post', '/impersonate');
  }

  public function testImpersonateReset()
  {
      Castle_RequestTransport::setResponse(204, '');
      Castle::impersonate(array('user_id' => '1', 'reset' => true));
      $this->assertRequest('delete', '/impersonate');
  }
}
