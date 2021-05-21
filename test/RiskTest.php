<?php

class CastleRiskTest extends Castle_TestCase
{
  public function tearDown(): void
  {
    Castle_RequestTransport::reset();
    Castle_RequestTransport::setResponse(200, '');
  }

  public function testApprove()
  {
    $risk = new Castle_Risk();
    $risk->save();
    $this->assertRequest('post', '/risk');
  }
}
