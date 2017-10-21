<?php

class CastleRequestTest extends \Castle_TestCase
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

  public function setUp()
  {
    $_COOKIE = array();
    $_SESSION = array();
  }

  public function tearDown()
  {
    Castle_RequestTransport::setResponse();
  }

  public function headersContains($keyword)
  {
    $request = Castle_RequestTransport::getLastRequest();
    if (!empty($request)) {
      return array_key_exists($keyword, $request['headers']);
    }
    return false;
  }

  public function testRequestContextIp()
  {
    Castle::track(array('name' => '$login.failed'));
    $this->assertRequest('post', '/track', array('X-Castle-Ip' => '8.8.8.8'));
  }

  public function testRequestContextForwardedIp()
  {
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.1.1.1';
    Castle::track(array('name' => '$login.failed'));
    $this->assertRequest('post', '/track', array('X-Castle-Ip' => '1.1.1.1'));
  }

  public function testRequestContextRealIp()
  {
    $_SERVER['HTTP_X_REAL_IP'] = '2.2.2.2';
    Castle::track(array('name' => '$login.failed'));
    $this->assertRequest('post', '/track', array('X-Castle-Ip' => '2.2.2.2'));
  }

  /**
   *
   */
  public function testRequestContextHeaders()
  {
    $_SERVER['HTTP_COOKIE'] = 'Should not be sent';
    Castle::track(array(
      'name' => '$login.succeeded',
      'user_id' => '1'
    ));
    $this->assertRequest('post', '/track', array('X-Castle-Headers' => '{"User-Agent":"TestAgent"}'));
  }

  /**
   *
   */
  public function testWhitelistHeaders()
  {
    $_SERVER['HTTP_AWESOME_HEADER'] = '14M4W350M3';

    Castle::setUseWhitelist(true);
    Castle::$whitelistHeaders[] = 'Awesome-Header';

    Castle::track(array(
      'name' => '$login.succeeded',
      'user_id' => '1'
    ));
    $this->assertRequest(
      'post',
      '/track',
      array('X-Castle-Headers' => '{"User-Agent":"TestAgent","Awesome-Header":"14M4W350M3"}')
    );
  }

  /**
   * @expectedException Castle_CurlOptionError
   */
  public function testCastleCurlOptions()
  {
    // Will not throw.
    Castle::setCurlOpts(array(CURLOPT_CONNECTTIMEOUT => 1,
                              CURLOPT_CONNECTTIMEOUT_MS => 1000,
                              CURLOPT_TIMEOUT => 1,
                              CURLOPT_TIMEOUT_MS => 1000));
    // Will throw.
    Castle::setCurlOpts(array(CURLOPT_USERAGENT => "BadBrowser/6.6.6b"));
  }

  public function testGetClientIDFromCookie()
  {
    $cookies = Castle::getCookieStore();

    $testUUID = '85B126D3-C706-4DBA-A352-883EFBCA9203';
    $cookies->write('__cid', $testUUID);

    Castle::track(array(
      'name' => '$login.succeeded',
      'user_id' => '1'
    ));

    $this->assertRequest(
      'post',
      '/track',
      array('X-Castle-Client-Id' => $testUUID)
    );
  }

  public function testGetClientIDInvalidFromCookie()
  {
    $cookies = Castle::getCookieStore();

    $testInvalidID = " \t\n\r\0\x0B";
    $cookies->write('__cid', $testInvalidID);

    Castle::track(array(
      'name' => '$login.succeeded',
      'user_id' => '1'
    ));

    $this->assertRequest(
      'post',
      '/track',
      array('X-Castle-Client-Id' => '_')
    );
  }

  public function testGetClientIDFromHeader()
  {
    $testUUID = '85B126D3-C706-4DBA-A352-883EFBCA9203';
    $_SERVER['HTTP_X_CASTLE_CLIENT_ID'] = $testUUID;

    Castle::track(array(
      'name' => '$login.succeeded',
      'user_id' => '1'
    ));

    $this->assertRequest(
      'post',
      '/track',
      array('X-Castle-Client-Id' => $testUUID)
    );
  }

   public function testGetClientIDInvalidFromHeader()
  {
    $testInvalidID = " \t\n\r\0\x0B";
    $_SERVER['HTTP_X_CASTLE_CLIENT_ID'] = $testInvalidID;

    Castle::track(array(
      'name' => '$login.succeeded',
      'user_id' => '1'
    ));

    $this->assertRequest(
      'post',
      '/track',
      array('X-Castle-Client-Id' => '_')
    );
  }

    public function testGetClientIDNoClientID()
  {

    Castle::track(array(
      'name' => '$login.succeeded',
      'user_id' => '1'
    ));

    $this->assertRequest(
      'post',
      '/track',
      array('X-Castle-Client-Id' => '?')
    );
  }

  /**
   * @expectedException Castle_ApiError
   */
  public function testInvalidResponse()
  {
    Castle_RequestTransport::setResponse(200, '{invalid');
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Castle_ApiError
   */
  public function testApiErrorRequest()
  {
    Castle_RequestTransport::setResponse(500);
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Castle_UnauthorizedError
   */
  public function testUnauthorizedRequest()
  {
    Castle_RequestTransport::setResponse(401);
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Castle_ForbiddenError
   */
  public function testForbiddenRequest()
  {
    Castle_RequestTransport::setResponse(403);
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  /**
   * @expectedException Castle_InvalidParametersError
   */
  public function testInvalidParametersRequest()
  {
    Castle_RequestTransport::setResponse(422);
    $req = new Castle_Request();
    $req->send('GET', '/users');
  }

  public function testRequestHeaders() {
    $req = new Castle_Request();
    $raw = $req->send('POST', '/events');
    $this->assertTrue($this->headersContains('X-Castle-Ip'));
    $this->assertTrue($this->headersContains('X-Castle-User-Agent'));
    $this->assertTrue($this->headersContains('X-Castle-Headers'));
    $this->assertTrue($this->headersContains('X-Castle-Client-Id'));
  }
}
