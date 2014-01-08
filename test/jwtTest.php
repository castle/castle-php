<?

require_once 'userbin.php';

class UserbinJWTTest extends \PHPUnit_Framework_TestCase
{

  public static function setUpBeforeClass() {
    Userbin::set_app_id('123456789');
    Userbin::set_api_secret('SECRET');
  }

  public function testInvalidJWT() {
    $jwt = new UserbinJWT('1234.123.432');
    $this->assertFalse($jwt->is_valid());
    $jwt = new UserbinJWT(null);
    $this->assertFalse($jwt->is_valid());
  }

  public function testValidJWT() {
    $jwt = new UserbinJWT('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiSm9obiBEb2UifQ.N2njxOAsbphq8_Aiv4YKaGu1x3EuMyt7JuIM2RLLmnA');
    $this->assertTrue($jwt->is_valid());
  }

  public function testValidJWTPayload() {
    $jwt = new UserbinJWT('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiSm9obiBEb2UifQ.N2njxOAsbphq8_Aiv4YKaGu1x3EuMyt7JuIM2RLLmnA');
    $payload = $jwt->payload();
    $this->assertEquals($payload['name'], 'John Doe');
  }
}

?>