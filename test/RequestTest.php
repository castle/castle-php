<?php

class CastleRequestTest extends \Castle_TestCase
{

  public static function setUpBeforeClass(): void
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    Castle::setApiKey('secretkey');
    Castle::setCurlOpts(array());
    Castle::setUseAllowlist(false);
  }

  public function setUp(): void
  {
    $_COOKIE = array();
    $_SESSION = array();
  }

  public function tearDown(): void
  {
    Castle_RequestTransport::setResponse();
  }

  public function testCastleCurlOptions()
  {
    // Will not throw.
    Castle::setCurlOpts(array(CURLOPT_CONNECTTIMEOUT => 1,
                              CURLOPT_CONNECTTIMEOUT_MS => 1000,
                              CURLOPT_TIMEOUT => 1,
                              CURLOPT_TIMEOUT_MS => 1000));
    // Will throw.
    $this->expectException(Castle_CurlOptionError::class);
    Castle::setCurlOpts(array(CURLOPT_USERAGENT => "BadBrowser/6.6.6b"));
  }

  public function testInvalidResponse()
  {
    Castle_RequestTransport::setResponse(200, '{invalid');
    $req = new Castle_Request();

    $this->expectException(Castle_ApiError::class);
    $req->send('GET', '/users');
  }

  public function testApiErrorRequest()
  {
    Castle_RequestTransport::setResponse(500);
    $req = new Castle_Request();

    $this->expectException(Castle_ApiError::class);
    $req->send('GET', '/users');
  }

  public function testUnauthorizedRequest()
  {
    Castle_RequestTransport::setResponse(401);
    $req = new Castle_Request();

    $this->expectException(Castle_UnauthorizedError::class);
    $req->send('GET', '/users');
  }

  public function testForbiddenRequest()
  {
    Castle_RequestTransport::setResponse(403);
    $req = new Castle_Request();

    $this->expectException(Castle_ForbiddenError::class);
    $req->send('GET', '/users');
  }

  public function testInvalidParametersRequest()
  {
    Castle_RequestTransport::setResponse(422);
    $req = new Castle_Request();
    $this->expectException(Castle_InvalidParametersError::class);
    $req->send('GET', '/users');
  }
}
