<?php

class CastleAuthenticationTest extends Castle_TestCase
{
  public function tearDown()
  {
    Castle_RequestTransport::reset();
    Castle_RequestTransport::setResponse(204, '');
  }

  public function testApprove()
  {
    $auth = (new Castle_Authentication(1))->approve();
    $this->assertRequest('post', '/authentications/1/approve');
    $this->assertEquals($auth->id, 1);
  }

  public function testDeny()
  {
    $auth = (new Castle_Authentication(1))->deny();
    $this->assertRequest('post', '/authentications/1/deny');
    $this->assertEquals($auth->id, 1);
  }

  public function testReset()
  {
    $auth = (new Castle_Authentication(1))->reset();
    $this->assertRequest('post', '/authentications/1/reset');
    $this->assertEquals($auth->id, 1);
  }
}
