<?php

class UserbinPairingTest extends Userbin_TestCase
{
  public static function setUpBeforeClass()
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
  }

  public function tearDown()
  {
    Userbin_RequestTransport::reset();
  }

  public function exampleUser()
  {
    return array(
      array(array(
        'id' => 1,
        'email' => 'hello@example.com'
      ))
    );
  }

  public function examplePairing()
  {
    return array(
      array(array(
        'id' => 1
      ))
    );
  }

  /**
   * @dataProvider exampleUser
   */
  public function testCreatePairing($userData)
  {
    $user = new Userbin_User($userData);
    $user->pairings()->create();
    $this->assertRequest('post', '/users/'.$user->id.'/pairings');
  }

  /**
   * @dataProvider examplePairing
   */
  public function testVerifyPairing($pairingData)
  {
    $pairing = new Userbin_Pairing($pairingData);
    $pairing->verify(array('response' => '123456'));
    $this->assertRequest('post', '/pairings/'.$pairing->id.'/verify');
  }

  /**
   * @dataProvider examplePairing
   */
  public function testSetDefaultPairing($pairingData)
  {
    $pairing = new Userbin_Pairing($pairingData);
    $pairing->setDefault();
    $this->assertRequest('post', '/pairings/'.$pairing->id.'/set_default');
  }
}
