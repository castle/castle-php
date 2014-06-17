<?php

class UserbinErrorTest extends Userbin_TestCase
{

  public function setUp()
  {
    $_SERVER = array();
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    Userbin_RequestTransport::reset();
    $this->request = new Userbin_Request();
  }

  /**
   * @expectedException Userbin_BadRequest
   */
  public function testBadRequest()
  {
    Userbin_RequestTransport::setResponse(400);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Userbin_UnauthorizedError
   */
  public function testUnauthorized()
  {
    Userbin_RequestTransport::setResponse(401);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Userbin_ForbiddenError
   */
  public function testForbidden()
  {
    Userbin_RequestTransport::setResponse(403);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Userbin_NotFoundError
   */
  public function testNotFound()
  {
    Userbin_RequestTransport::setResponse(404);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Userbin_UserUnauthorizedError
   */
  public function testUserUnauthorized()
  {
    Userbin_RequestTransport::setResponse(419);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Userbin_InvalidParametersError
   */
  public function testInvalidParameters()
  {
    Userbin_RequestTransport::setResponse(422);
    $this->request->send('GET', '/test');
  }

  /**
   * @expectedException Userbin_ConfigurationError
   */
  public function testConfiguration()
  {
    Userbin::setApiKey(null);
    $this->request->send('GET', '/test');
  }
}