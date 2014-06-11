<?php

class UserbinRequestTest extends \PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass()
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
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

  /**
   */
  public function testRequestHeaders() {
    $req = new Userbin_Request();
    $raw = $req->send('GET', '/users');
    $this->assertTrue($this->headersContains('X-Userbin-Ip'));
    $this->assertTrue($this->headersContains('X-Userbin-User-Agent'));
  }
}

?>