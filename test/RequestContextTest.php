<?php

class CastleRequestContextTest extends \Castle_TestCase
{

  public static function setUpBeforeClass()
  {
    $_SERVER = array();
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
    Castle::setApiKey('secretkey');
    Castle::setCurlOpts(array());
    Castle::setUseWhitelist(false);
  }

  public function exampleJson() {
    return array(array('{"clientId":"?","ip":"8.8.8.8","headers":"{\"User-Agent\":\"TestAgent\"}","body":"{\"user_id\":1}"}'));
  }

  public function testBuild() {
    $instance = Castle_RequestContext::build(array(
      'ip' => '8.8.8.8',
      'clientId' => 'abcd',
      'headers' => '{}',
      'body' => '{}'
    ));

    $this->assertEquals($instance->ip, '8.8.8.8');
    $this->assertEquals($instance->clientId, 'abcd');
    $this->assertEquals($instance->headers, '{}');
    $this->assertEquals($instance->body, '{}');
  }

  /**
   * @dataProvider exampleJson
   */
  public function testExtractJson($expected) {
    $json = Castle_RequestContext::extractJson(array('user_id' => 1));

    $this->assertEquals($json, $expected);
  }

  /**
   * @dataProvider exampleJson
   */
  public function testFromJson($json) {
    $instance = Castle_RequestContext::fromJson($json);

    $this->assertEquals($instance->ip, '8.8.8.8');
    $this->assertEquals($instance->clientId, '?');
    $this->assertEquals($instance->headers, '{"User-Agent":"TestAgent"}');
    $this->assertEquals($instance->body, '{"user_id":1}');
  }

}
