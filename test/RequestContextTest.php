<?php

class CastleRequestContextTest extends \Castle_TestCase
{

  public static function setUpBeforeClass()
  {
    $_SERVER = array();
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    $_SERVER['HTTP_X_CASTLE_CLIENT_ID'] = '1ccf8dee-904b-4d20-8a88-55ded468bcc5';
  }

  public function contextProvider() {
    return array(array(array(
        'clientId' => '1ccf8dee-904b-4d20-8a88-55ded468bcc5',
        'ip' => '8.8.8.8',
        'headers' => array(
          'User-Agent' => 'TestAgent',
          'X-Castle-Client-Id' => '1ccf8dee-904b-4d20-8a88-55ded468bcc5'
        ),
        'user_agent' => 'TestAgent',
        'library' => array(
          'name' => 'castle-php',
          'version' => Castle::VERSION
        )
      )));
  }

  public function contextJsonProvider() {
    return array(array('{"clientId":"1ccf8dee-904b-4d20-8a88-55ded468bcc5","ip":"8.8.8.8","headers":{"User-Agent":"TestAgent","X-Castle-Client-Id":"1ccf8dee-904b-4d20-8a88-55ded468bcc5"},"user_agent":"TestAgent","library":{"name":"castle-php","version":"1.4.4"}}'));
  }

  /**
   * @dataProvider contextProvider
   */
  public function testExtract($expected) {
    $actual = Castle_RequestContext::extract();
    $this->assertEquals($expected, $actual);
  }


  /**
   * @dataProvider contextJsonProvider
   */
  public function testExtractJson($expected) {
    $actual = Castle_RequestContext::extractJson();
    $this->assertEquals($expected, $actual);
  }

}
