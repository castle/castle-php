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
        'client_id' => '1ccf8dee-904b-4d20-8a88-55ded468bcc5',
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
    return array(array('{"client_id":"1ccf8dee-904b-4d20-8a88-55ded468bcc5","ip":"8.8.8.8","headers":{"User-Agent":"TestAgent","X-Castle-Client-Id":"1ccf8dee-904b-4d20-8a88-55ded468bcc5"},"user_agent":"TestAgent","library":{"name":"castle-php","version":"3.0.0"}}'));
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

  public function testExtractClientIDFromCookie() {
    unset($_SERVER['HTTP_X_CASTLE_CLIENT_ID']);
    $expected = $testUUID = '85B126D3-C706-4DBA-A352-883EFBCA9203';

    $cookies = Castle::getCookieStore();
    $cookies->write('__cid', $testUUID);

    $actual = Castle_RequestContext::extractClientId();

    $this->assertEquals($expected, $actual);
  }

  public function testExtractClientIDInvalidFromCookie() {
    unset($_SERVER['HTTP_X_CASTLE_CLIENT_ID']);
    $expected = '_';
    $testInvalidID = " \t\n\r\0\x0B";

    $cookies = Castle::getCookieStore();
    $cookies->write('__cid', $testInvalidID);

    $actual = Castle_RequestContext::extractClientId();

    $this->assertEquals($expected, $actual);
  }

  public function testExtractClientIDFromHeader() {
    $expected = $testUUID = '85B126D3-C706-4DBA-A352-883EFBCA9203';

    $_SERVER['HTTP_X_CASTLE_CLIENT_ID'] = $testUUID;

    $actual = Castle_RequestContext::extractClientId();

    $this->assertEquals($expected, $actual);
  }

  public function testExtractClientIDInvalidFromHeader() {
    $expected = '_';
    $testInvalidID = " \t\n\r\0\x0B";

    $_SERVER['HTTP_X_CASTLE_CLIENT_ID'] = $testInvalidID;

    $actual = Castle_RequestContext::extractClientId();

    $this->assertEquals($expected, $actual);
  }

  public function testExtractClientIDNoClientID() {
    unset($_SERVER['HTTP_X_CASTLE_CLIENT_ID']);
    $expected = '?';
    $actual = Castle_RequestContext::extractClientId();

    $this->assertEquals($expected, $actual);
  }

  public function testExtractIp() {
    $expected = '8.8.8.8';
    $actual = Castle_RequestContext::extractIp();

    $this->assertEquals($expected, $actual);
  }

  public function testExtractIpFromForwardedIp() {
    $expected = '1.1.1.1';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = $expected;

    $actual = Castle_RequestContext::extractIp();

    $this->assertEquals($expected, $actual);
  }

  public function testExtractIpFromRealIp() {
    $expected = '2.2.2.2';
    $_SERVER['HTTP_X_REAL_IP'] = $expected;
    unset($_SERVER['HTTP_X_FORWARDED_FOR']);

    $actual = Castle_RequestContext::extractIp();

    $this->assertEquals($expected, $actual);
  }


  /**
   * dataProvider headersProvider
   */
  public function testExtractHeadersWithoutCookie() {
    $expected = array('User-Agent' => 'TestAgent');
    $_SERVER['HTTP_COOKIE'] = 'Should not be included';
    unset($_SERVER['HTTP_X_REAL_IP']);
    unset($_SERVER['HTTP_X_CASTLE_CLIENT_ID']);
    $actual = Castle_RequestContext::extractHeaders();

    $this->assertEquals($expected, $actual);
  }

  /**
   *
   */
  public function testAllowlistHeaders() {
    $expected = array(
        'User-Agent' => 'TestAgent',
        'Awesome-Header' => '14M4W350M3'
      );
    $_SERVER['HTTP_AWESOME_HEADER'] = '14M4W350M3';

    Castle::setUseAllowlist(true);
    Castle::$allowlistedHeaders[] = 'Awesome-Header';

    $actual = Castle_RequestContext::extractHeaders();

    $this->assertEquals($expected, $actual);
  }
}
