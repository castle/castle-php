<?php

class UserbinJWTTest extends \PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass() {
    Userbin::setApiKey('secretkey');
  }

  public function invalidJWTs() {
    return array(
      array('1234.123.432'),
      array(null)
    );
  }

  public function validJWTs() {
    return array(
      array('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlhdCI6MTM5ODIzOTIwMywiZXhwIjoxMzk4MjQyODAzfQ.eyJ1c2VyX2lkIjoiZUF3djVIdGRiU2s4Yk1OWVpvanNZdW13UXlLcFhxS3IifQ.Apa7EmT5T1sOYz4Af0ERTDzcnUvSalailNJbejZ2ddQ', 'user_id', 'eAwv5HtdbSk8bMNYZojsYumwQyKpXqKr')
    );
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
   * @expectedException Userbin_SecurityError
   */
  public function testInvalidConstructorArgument()
  {
    $jwt = new Userbin_JWT('1234');
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

  public function testSetHeader()
  {
    $jwt = new Userbin_JWT();
    $now = time();
    $jwt->setHeader('exp', $now);
    $header = $jwt->getHeader();
    $this->assertEquals($header['exp'], $now);
  }

  public function testGetSetBody()
  {
    $jwt = new Userbin_JWT();
    $jwt->setBody('chg', '1234');
    $this->assertEquals($jwt->getBody('chg'), '1234');
    $this->assertTrue($jwt->isValid());
  }

  /**
   * @dataProvider validJWTs
   */
  public function testGetheaderWithKey($data)
  {
    $jwt = new Userbin_JWT($data);
    $this->assertEquals($jwt->getHeader('typ'), 'JWT');
  }

  public function testHasExpired()
  {
    $jwt = new Userbin_JWT();
    $jwt->setHeader(array('exp' => time() + 60));
    $this->assertFalse($jwt->hasExpired());
  }
}
