<?php

class CastleRequestTest extends \Castle_TestCase
{

  public static function setUpBeforeClass()
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    Castle::setApiKey('secretkey');
    Castle::setCurlOpts(array());
    Castle::setUseWhitelist(false);
  }

  public function setUp()
  {
    $_COOKIE = array();
    $_SESSION = array();
  }

  public function tearDown()
  {
    Castle_RequestTransport::setResponse();
  }

  /**
   * @expectedException Castle_CurlOptionError
   */
  public function testCastleCurlOptions()
  {
    // Will not throw.
    Castle::setCurlOpts(array(CURLOPT_CONNECTTIMEOUT => 1,
                              CURLOPT_CONNECTTIMEOUT_MS => 1000,
                              CURLOPT_TIMEOUT => 1,
                              CURLOPT_TIMEOUT_MS => 1000));
    // Will throw.
    Castle::setCurlOpts(array(CURLOPT_USERAGENT => "BadBrowser/6.6.6b"));
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
   * @expectedException Castle_InvalidParametersError
   */
  public function testInvalidParametersRequest()
  {
    Castle_RequestTransport::setResponse(422);
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }
}
