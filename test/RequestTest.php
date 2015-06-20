<?php

class CastleRequestTest extends \Castle_TestCase
{

  public static function setUpBeforeClass()
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    Castle::setApiKey('secretkey');
  }

  public function setUp()
  {
    $_SESSION = array();
  }

  public function tearDown()
  {
    Castle_RequestTransport::setResponse();
  }

  public function headersContains($keyword)
  {
    $request = Castle_RequestTransport::getLastRequest();
    if (!empty($request)) {
      return array_key_exists($keyword, $request['headers']);
    }
    return false;
  }

  public function exampleSessionToken()
  {
    return array(array('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s'));
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testRequestContextIp($sessionToken)
  {
    Castle::track(array('name' => '$login.failed'));
    $this->assertRequest('post', '/events', array('X-Castle-Ip' => '8.8.8.8'));
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testRequestContextForwardedIp($sessionToken)
  {
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.1.1.1';
    Castle::track(array('name' => '$login.failed'));
    $this->assertRequest('post', '/events', array('X-Castle-Ip' => '1.1.1.1'));
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testRequestContextRealIp($sessionToken)
  {
    Castle::getTokenStore()->setSession($sessionToken);
    $_SERVER['HTTP_X_REAL_IP'] = '2.2.2.2';
    Castle::track(array('name' => '$login.failed'));
    $this->assertRequest('post', '/events', array('X-Castle-Ip' => '2.2.2.2'));
  }

  /**
   *
   */
  public function testRequestContextHeaders()
  {
    $_SERVER['HTTP_COOKIE'] = 'Should not be sent';
    Castle::track(array(
      'name' => '$login.succeeded',
      'user_id' => '1'
    ));
    $this->assertRequest('post', '/events', array('X-Castle-Headers' => '{"User-Agent":"TestAgent"}'));
  }

  /**
   * @expectedException Castle_ApiError
   */
  public function testInvalidResponse()
  {
    Castle_RequestTransport::setResponse(200, '{invalid');
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Castle_ApiError
   */
  public function testApiErrorRequest()
  {
    Castle_RequestTransport::setResponse(500);
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Castle_UnauthorizedError
   */
  public function testUnauthorizedRequest()
  {
    Castle_RequestTransport::setResponse(401);
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Castle_ForbiddenError
   */
  public function testForbiddenRequest()
  {
    Castle_RequestTransport::setResponse(403);
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Castle_UserUnauthorizedError
   */
  public function testUserUnauthorizedRequest()
  {
    Castle_RequestTransport::setResponse(419);
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Castle_InvalidParametersError
   */
  public function testInvalidParametersRequest()
  {
    Castle_RequestTransport::setResponse(422);
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  public function testRequestHeaders() {
    $req = new Castle_Request();
    $raw = $req->send('GET', '/users');
    $this->assertTrue($this->headersContains('X-Castle-Ip'));
    $this->assertTrue($this->headersContains('X-Castle-User-Agent'));
  }
}
