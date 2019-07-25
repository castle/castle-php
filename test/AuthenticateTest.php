<?php

class CastleAuthenticateTest extends Castle_TestCase
{
  public function tearDown(): void
  {
    Castle_RequestTransport::reset();
    Castle_RequestTransport::setResponse(200, '');
  }

  public function testApprove()
  {
    $auth = new Castle_Authenticate();
    $auth->save();
    $this->assertRequest('post', '/authenticate');
  }
}
