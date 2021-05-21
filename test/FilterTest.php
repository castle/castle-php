<?php

class CastleFilterTest extends Castle_TestCase
{
  public function tearDown(): void
  {
    Castle_RequestTransport::reset();
    Castle_RequestTransport::setResponse(200, '');
  }

  public function testApprove()
  {
    $filter = new Castle_Filter();
    $filter->save();
    $this->assertRequest('post', '/filter');
  }
}
