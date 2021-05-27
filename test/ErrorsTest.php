<?php

class CastleErrorTest extends Castle_TestCase
{

  public function setUp(): void
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    Castle_RequestTransport::reset();
    $this->request = new Castle_Request();
  }

  public function testBadRequest()
  {
    Castle_RequestTransport::setResponse(400);

    $this->expectException(Castle_BadRequest::class);
    $this->request->send('GET', '/test');
  }

  public function testUnauthorized()
  {
    Castle_RequestTransport::setResponse(401);

    $this->expectException(Castle_UnauthorizedError::class);
    $this->request->send('GET', '/test');
  }

  public function testForbidden()
  {
    Castle_RequestTransport::setResponse(403);

    $this->expectException(Castle_ForbiddenError::class);
    $this->request->send('GET', '/test');
  }

  public function testNotFound()
  {
    Castle_RequestTransport::setResponse(404);

    $this->expectException(Castle_NotFoundError::class);
    $this->request->send('GET', '/test');
  }

  public function testInvalidParameters()
  {
    Castle_RequestTransport::setResponse(422);

    $this->expectException(Castle_InvalidParametersError::class);
    $this->request->send('GET', '/test');
  }

  public function testConfiguration()
  {
    Castle::setApiKey(null);

    $this->expectException(Castle_ConfigurationError::class);
    $this->request->send('GET', '/test');
  }
}
