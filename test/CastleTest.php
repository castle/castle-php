<?php

class CastleTest extends Castle_TestCase
{
  protected $sessionToken;

  public static function setUpBeforeClass() {
    Castle::setApiKey('secretkey');
  }

  public function setUp()
  {
    $this->sessionToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s';
    $_SESSION = array();
    $_COOKIE = array();
  }

  public function testSetApiKey()
  {
    $this->assertContains('secretkey', Castle::getApiKey());
  }

  public function exampleUser()
  {
    return array(
      array(array('id' => 'user-2412', 'email' => 'hello@example.com'))
    );
  }

  public function exampleSessionToken()
  {
    return array(array('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s'));
  }

  public function exampleSessionTokenWithMFA()
  {
    Castle::setApiKey('secretkey');
    $jwt = new Castle_JWT();
    $jwt->setHeader(array('iss' => '1', 'exp' => time()));
    $jwt->setBody('vfy', 1);
    return array(array($jwt->toString()));
  }

  public function exampleSessionTokenWithChallenge()
  {
    Castle::setApiKey('secretkey');
    $jwt = new Castle_JWT();
    $jwt->setHeader(array('iss' => '1', 'exp' => time()));
    $jwt->setBody('chg', 1);
    $jwt->setBody('dpr', 1);
    $jwt->setBody('mfa', 1);
    $jwt->setBody('vfy', 1);
    $jwt->setBody('typ', 'authenticator');
    $jwt->isValid();
    return array(array($jwt->toString()));
  }

  public function testTrack()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::track(array('name' => '$login.failed'));
    $this->assertRequest('post', '/events');
  }

  public function testIdentify()
  {
    Castle_RequestTransport::setResponse(204, '');
    Castle::identify(1, array('name' => 'John Doe'));
    $this->assertRequest('put', '/users/1');
  }
}
