<?php

class UserbinTest extends Userbin_TestCase
{
  protected $sessionToken;

  public static function setUpBeforeClass() {
    Userbin::setApiKey('secretkey');
  }

  public function setUp()
  {
    $this->sessionToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6InVzZXItMjQxMiIsInN1YiI6IlMyb2R4UmVabkdxaHF4UGFRN1Y3a05rTG9Ya0daUEZ6IiwiYXVkIjoiODAwMDAwMDAwMDAwMDAwIiwiZXhwIjoxMzk5NDc5Njc1LCJpYXQiOjEzOTk0Nzk2NjUsImp0aSI6MH0.eyJjaGFsbGVuZ2UiOnsiaWQiOiJUVENqd3VyM3lwbTRUR1ZwWU43cENzTXFxOW9mWEVBSCIsInR5cGUiOiJvdHBfYXV0aGVudGljYXRvciJ9fQ.LT9mUzJEbsizbFxcpMo3zbms0aCDBzfgMbveMGSi1-s';
    $_SESSION = array();
  }

  public function testSetApiKey()
  {
    $this->assertContains('secretkey', Userbin::getApiKey());
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
    $jwt = new Userbin_JWT();
    $jwt->setHeader(array('iss' => '1', 'exp' => time()));
    $jwt->setBody('vfy', 1);
    return array(array($jwt->toString()));
  }

  public function exampleSessionTokenWithChallenge()
  {
    $jwt = new Userbin_JWT();
    $jwt->setHeader(array('iss' => '1', 'exp' => time()));
    $jwt->setBody('chg', 1);
    $jwt->setBody('vfy', 1);
    $jwt->setBody('typ', 'authenticator');
    return array(array($jwt->toString()));
  }

  /**
   * @dataProvider exampleUser
   */
  public function testLoginWithoutExistingSession($userData)
  {
    Userbin_RequestTransport::setResponse(201, array('token' => $this->sessionToken));

    $user = Userbin::login($userData['id'], $userData);
    $this->assertEquals($user->id, $userData['id']);
    $this->assertRequest('post', '/users/'.$userData['id'].'/sessions');
    $this->assertInstanceOf('Userbin_SessionToken', Userbin::getSessionToken());
  }

  /**
   * @dataProvider exampleUser
   */
  public function testLoginSendsUserData($userData)
  {
    $user = Userbin::login($userData['id'], $userData);
    $request = $this->assertRequest('post', '/users/'.$userData['id'].'/sessions');
    $this->assertEquals($request['params']['user'], $userData);
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testAuthorizeWithExistingSession($token)
  {
    $_SESSION['userbin'] = $token;
    Userbin::authorize();
    $this->assertRequest('post', '/heartbeat', array('X-Userbin-Session-Token' => $token));
  }


  /**
   * @expectedException Userbin_UserUnauthorizedError
   */
  public function testAuthorizeWithoutSession()
  {
    Userbin::authorize();
  }

  /**
   * @dataProvider exampleSessionTokenWithMFA
   * @expectedException Userbin_ChallengeRequiredError
   */
  public function testAuthorizeWithMFASession($token)
  {
    $_SESSION['userbin'] = $token;
    Userbin::authorize();
  }

  /**
   * @dataProvider exampleSessionTokenWithChallenge
   * @expectedException Userbin_UserUnauthorizedError
   */
  public function testAuthorizeWithChallengeSession($token)
  {
    $_SESSION['userbin'] = $token;
    Userbin::authorize();
  }

  /**
   * @dataProvider exampleSessionToken
   */
  public function testLogout($token)
  {
    Userbin::getSessionStore()->write($token);
    $session = Userbin::getSessionToken();
    Userbin::logout();
    $this->assertRequest('delete', '/sessions/'.$session->getId());
    $this->assertFalse(array_key_exists('userbin', $_SESSION));
  }
}
