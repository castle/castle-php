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
    $this->assertRequest('post', '/track');
  }

  public function testAuthenticate()
  {
    Castle_RequestTransport::setResponse(201, '{ "status": "approve" }');
    $auth = Castle::authenticate(Array(
      'user_id' => '1',
      'name' => '$login.failed'
    ));
    $this->assertRequest('post', '/authenticate');
    $this->assertEquals($auth->status, 'approve');
  }

  public function testLegacyIdentify()
  {
    Castle_RequestTransport::setResponse(204);
    $auth = Castle::identify('1', Array(
      'traits' => Array('name' => 'Kalle Jularbo')
    ));
    $this->assertRequest('post', '/identify');
  }

  public function testIdentify()
  {
    Castle_RequestTransport::setResponse(204);
    $auth = Castle::identify(Array(
      'user_id' => 1,
      'traits' => Array('name' => 'Kalle Jularbo')
    ));
    $this->assertRequest('post', '/identify');
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

  public function testReview()
  {
    Castle_RequestTransport::setResponse(200, '{ "id": "123553", "reviewed": true, "user_id" : "1234546", "context": {} }');
    $review = Castle::fetchReview('123553');
    $this->assertRequest('get', '/reviews/123553');
    $this->assertEquals($review->id, '123553');
  }
}
