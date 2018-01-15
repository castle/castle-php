<?php

class CastleErrorTest extends Castle_TestCase
{

  public function setUp()
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    Castle_RequestTransport::reset();
    $this->request = new Castle_Request();
  }

  /**
   * @expectedException Castle_BadRequest
   */
  public function testBadRequest()
  {
    Castle_RequestTransport::setResponse(400);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Castle_UnauthorizedError
   */
  public function testUnauthorized()
  {
    Castle_RequestTransport::setResponse(401);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Castle_ForbiddenError
   */
  public function testForbidden()
  {
    Castle_RequestTransport::setResponse(403);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Castle_NotFoundError
   */
  public function testNotFound()
  {
    Castle_RequestTransport::setResponse(404);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Castle_InvalidParametersError
   */
  public function testInvalidParameters()
  {
    Castle_RequestTransport::setResponse(422);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Castle_ConfigurationError
   */
  public function testConfiguration()
  {
    Castle::setApiKey(null);
    $this->request->send('GET', '/test');
  }
}
