<?php

class CastleLogTest extends Castle_TestCase
{
  public function tearDown(): void
  {
    Castle_RequestTransport::reset();
    Castle_RequestTransport::setResponse(200, '');
  }

  public function testApprove()
  {
    $log = new Castle_Log();
    $log->save();
    $this->assertRequest('post', '/log');
  }
}
