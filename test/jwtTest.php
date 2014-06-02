<?php

class UserbinJWTTest extends \PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass() {
    Userbin::setApiKey('secretkey');
  }

  public function invalidJWTs() {
    return [
      ['1234.123.432'],
      [null]
    ];
  }

  public function validJWTs() {
    return [
      ['eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlhdCI6MTM5ODIzOTIwMywiZXhwIjoxMzk4MjQyODAzfQ.eyJ1c2VyX2lkIjoiZUF3djVIdGRiU2s4Yk1OWVpvanNZdW13UXlLcFhxS3IifQ.Apa7EmT5T1sOYz4Af0ERTDzcnUvSalailNJbejZ2ddQ', 'user_id', 'eAwv5HtdbSk8bMNYZojsYumwQyKpXqKr']
    ];
  }

  /**
   * @dataProvider invalidJWTs
   * @expectedException Userbin_SecurityError
   */
  public function testInvalidJWT($data) {
    $jwt = new Userbin_JWT($data);
    $jwt->isValid();
  }

  /**
   * @dataProvider validJWTs
   */
  public function testValidJWT($data) {
    $jwt = new Userbin_JWT($data);
    $this->assertTrue($jwt->isValid());
  }

  /**
   * @dataProvider validJWTs
   */
  public function testValidJWTPayload($data, $key, $value) {
    $jwt = new Userbin_JWT($data);
    $payload = $jwt->getBody();
    $this->assertEquals($payload[$key], $value);
  }
}

?>