<?php

class UserbinRequestTest extends \Userbin_TestCase
{

  public static function setUpBeforeClass()
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    Userbin::setApiKey('secretkey');
  }

  public function setUp()
  {
    $_SESSION = array();
  }

  public function tearDown()
  {
    Userbin_RequestTransport::setResponse();
  }

  public function headersContains($keyword)
  {
    $request = Userbin_RequestTransport::getLastRequest();
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
  public function testRequestContent($sessionToken)
  {
    Userbin::getSessionStore()->write($sessionToken);
    Userbin::authorize();

    $this->assertRequest('post', '/heartbeat', array('Content-Length' => '0'));
    $request = Userbin_RequestTransport::getLastRequest();
    $this->assertEquals($request['body'], '');
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testRequestClearsSessionOnUserUnauthorized($sessionToken)
  {
    $Store = Userbin::getSessionStore();
    $Store->write($sessionToken);
    Userbin_RequestTransport::setResponse(419);
    try {
      Userbin::authorize();
    } catch (Exception $e) { }
    $this->assertEmpty($Store->read());
  }

  /**
   * @expectedException Userbin_ApiError
   */
  public function testInvalidResponse()
  {
    Userbin_RequestTransport::setResponse(200, '{invalid');
    $req = new Userbin_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Userbin_ApiError
   */
  public function testApiErrorRequest()
  {
    Userbin_RequestTransport::setResponse(500);
    $req = new Userbin_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Userbin_UnauthorizedError
   */
  public function testUnauthorizedRequest()
  {
    Userbin_RequestTransport::setResponse(401);
    $req = new Userbin_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Userbin_ForbiddenError
   */
  public function testForbiddenRequest()
  {
    Userbin_RequestTransport::setResponse(403);
    $req = new Userbin_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Userbin_UserUnauthorizedError
   */
  public function testUserUnauthorizedRequest()
  {
    Userbin_RequestTransport::setResponse(419);
    $req = new Userbin_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Userbin_InvalidParametersError
   */
  public function testInvalidParametersRequest()
  {
    Userbin_RequestTransport::setResponse(422);
    $req = new Userbin_Request();
    $req->send('GET', '/users');
  }

  public function testRequestHeaders() {
    $req = new Userbin_Request();
    $raw = $req->send('GET', '/users');
    $this->assertTrue($this->headersContains('X-Userbin-Ip'));
    $this->assertTrue($this->headersContains('X-Userbin-User-Agent'));
  }
}
