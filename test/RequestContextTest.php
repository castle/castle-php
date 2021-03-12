<?php

class CastleRequestContextTest extends \Castle_TestCase
{

  public static function setUpBeforeClass(): void {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    unset($_SERVER['HTTP_X_FORWARDED_FOR']);
  }

  public function setUp(): void {
    $_COOKIE = array();
  }

  public function contextProvider() {
    return array(array(array(
        'library' => array(
          'name' => 'castle-php',
          'version' => Castle::VERSION
        )
      )));
  }

  public function contextJsonProvider() {
    return array(array('{"library":{"name":"castle-php","version":"2.1.0"}}'));
  }

  /**
   * @dataProvider contextProvider
   */
  public function testExtract($expected) {
    $_SERVER['HTTP_X_CASTLE_CLIENT_ID'] = '1ccf8dee-904b-4d20-8a88-55ded468bcc5';

    $actual = Castle_RequestContext::extract();
    $this->assertEquals($expected, $actual);
  }


  /**
   * @dataProvider contextJsonProvider
   */
  public function testExtractJson($expected) {
    $_SERVER['HTTP_X_CASTLE_CLIENT_ID'] = '1ccf8dee-904b-4d20-8a88-55ded468bcc5';

    $actual = Castle_RequestContext::extractJson();
    $this->assertEquals($expected, $actual);
  }
}
