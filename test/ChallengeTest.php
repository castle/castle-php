<?php

class CastleChallengeTest extends Castle_TestCase
{
  public static function setUpBeforeClass() {
    Castle::setApiKey('secretkey');
    $_SERVER = array();
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
  }

  public function tearDown()
  {
    Castle_RequestTransport::reset();
  }

  public function testValidate()
  {
    $challenge = new Castle_Challenge(1);
    $challenge->verify('1234');
    $request = $this->assertRequest('post', '/challenges/1/verify');
    $this->assertArrayHasKey('response', $request['params']);
    $this->assertEquals($request['params']['response'], '1234');
  }

  public function testValidateWithoutId()
  {
    $challenge = new Castle_Challenge();
    $this->assertFalse($challenge->verify('1234'));
  }
}
