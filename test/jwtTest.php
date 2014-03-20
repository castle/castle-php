<?php

require_once 'userbin.php';

class UserbinJWTTest extends \PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass() {
    Userbin::set_app_id('123456789');
    Userbin::set_api_secret('SECRET');
  }

  public function invalidJWTs() {
    return [
      ['1234.123.432'],
      [null]
    ];
  }

  public function validJWTs() {
    return [
      ['eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiSm9obiBEb2UifQ.N2njxOAsbphq8_Aiv4YKaGu1x3EuMyt7JuIM2RLLmnA', 'name', 'John Doe']
    ];
  }

  /**
   * @dataProvider invalidJWTs
   */
  public function testInvalidJWT($data) {
    $jwt = new UserbinJWT($data);
    $this->assertFalse($jwt->is_valid());
  }

  /**
   * @dataProvider validJWTs
   */
  public function testValidJWT($data) {
    $jwt = new UserbinJWT($data);
    $this->assertTrue($jwt->is_valid());
  }

  /**
   * @dataProvider validJWTs
   */
  public function testValidJWTPayload($data, $key, $value) {
    $jwt = new UserbinJWT($data);
    $payload = $jwt->payload();
    $this->assertEquals($payload[$key], $value);
  }
}

?>