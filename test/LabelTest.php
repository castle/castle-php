<?php

class CastleLabelTest extends Castle_TestCase
{
  public function tearDown()
  {
    Castle_RequestTransport::reset();
  }

  public function testCreate()
  {
    $label = new Castle_Label(array(
      'user_id' => 1
    ));
    $label->save();
    $this->assertRequest('post', '/labels');
  }

  public function testDelete()
  {
    $label = new Castle_Label();
    $label->delete();
    $this->assertRequest('delete', '/labels');
  }
}
