<?php

class UserbinSessionTest extends Userbin_TestCase
{
  public static function setUpBeforeClass() {
    Userbin::setApiKey('secretkey');
  }

  public function setUp()
  {
    $_SESSION = array();
  }

  public function tearDown()
  {
    Userbin_RequestTransport::reset();
  }

  public function exampleSessionTokenWithChallenge()
  {
    Userbin::setApiKey('secretkey');
    $jwt = new Userbin_JWT();
    $jwt->setHeader(array(
      'mfa' => 1,
      'iss' => 1,
      'vfy' => 1
    ));
    $jwt->setBody('chg', '1');
    return array(array($jwt->toString()));
  }

  public function exampleSession()
  {
    $sessionToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s';
    return array(
      array(array(
        'id' => 1,
        'token' => $sessionToken,
        'user' => array(
          'id' => 1,
          'email' => 'hello@example.com'
        )
      ), $sessionToken)
    );
  }

  /**
   * @dataProvider exampleSession
   */
  public function testHasExpired($sessionData, $sessionToken)
  {
    $session = new Userbin_SessionToken($sessionToken);
    $this->assertTrue($session->hasExpired());
  }

  /**
   * @dataProvider exampleSession
   */
  public function testUser($sessionData, $sessionToken)
  {
    $session = new Userbin_SessionToken($sessionToken);
    $this->assertTrue($session->getUser() instanceof Userbin_User);
  }

  /**
   * @dataProvider exampleSession
   */
  public function testGetChallengeWithoutChallenge($sessionData, $sessionToken)
  {
    $session = new Userbin_SessionToken($sessionToken);
    $this->assertNull($session->getChallenge());
  }

  /**
   * @dataProvider exampleSessionTokenWithChallenge
   */
  public function testGetChallengeWithChallenge($sessionToken)
  {
    $session = new Userbin_SessionToken($sessionToken);
    $this->assertInstanceOf('Userbin_Challenge', $session->getChallenge());
    $this->assertEquals($session->getChallenge()->getId(), '1');
  }

  /**
   * @dataProvider exampleSessionTokenWithChallenge
   */
  public function testGetChallengeType($sessionToken)
  {
    $session = new Userbin_SessionToken($sessionToken);
    $this->assertEquals($session->getChallengeType(), 'authenticator');
    $this->assertEquals($session->getChallenge()->type, 'authenticator');
  }

  /**
   * @dataProvider exampleSession
   */
  public function testSetChallenge($sessionData, $sessionToken)
  {
    Userbin_RequestTransport::setResponse(201, array('id' => 1));
    $session = new Userbin_SessionToken($sessionToken);
    $challenge = $session->getUser()->challenges()->create();
    $session->setChallenge($challenge);
    $gotChallenge = $session->getChallenge();
    $this->assertEquals($challenge->getId(), $gotChallenge->getId());
  }

  /**
   * @dataProvider exampleSession
   */
  public function testUserId($sessionData, $sessionToken)
  {
    $session = new Userbin_SessionToken($sessionToken);
    $this->assertEquals($session->getUser()->getId(), 'user-2412');
  }

  /**
   * @dataProvider exampleSession
   */
  public function testLoad($sessionData, $sessionToken)
  {
    $session = new Userbin_SessionToken($sessionToken);
    $user = $session->getUser();
    $this->assertTrue($user instanceof Userbin_User);
  }
}
