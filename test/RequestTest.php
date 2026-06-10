<?php

class CastleRequestTest extends \Castle_TestCase
{

  public static function setUpBeforeClass(): void
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    Castle::setApiKey('secretkey');
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

  public function testInvalidRequestTokenRequest()
  {
    Castle_RequestTransport::setResponse(
      422, '{ "type": "invalid_request_token", "message": "" }'
    );
    $req = new Castle_Request();
    $this->expectException(Castle_InvalidRequestTokenError::class);
    $req->send('POST', '/risk');
  }
}
