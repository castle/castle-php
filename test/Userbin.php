<?php

abstract class Userbin_TestCase extends \PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    Userbin::setApiKey('secret');
  }

  public function assertRequest($method, $url, $headers=null)
  {
    $request = Userbin_RequestTransport::getLastRequest();
    $this->assertEquals($method, $request['method']);
    $this->assertEquals(Userbin_Request::apiUrl($url), $request['url']);
    if (is_array($headers)) {
      foreach ($headers as $key => $value) {
        $this->assertArrayHasKey($key, $request['headers']);
        $this->assertEquals($request['headers'][$key], $value);
      }
    }
    return $request;
  }
}

require(dirname(__FILE__) . '/../lib/Userbin/Userbin.php');
require(dirname(__FILE__) . '/../lib/Userbin/Errors.php');
require(dirname(__FILE__) . '/../lib/Userbin/SessionToken.php');
require(dirname(__FILE__) . '/../lib/Userbin/SessionStore.php');
require(dirname(__FILE__) . '/TrustedTokenStore.php');
require(dirname(__FILE__) . '/../lib/Userbin/Resource.php');
require(dirname(__FILE__) . '/../lib/RestModel/Model.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Account.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/BackupCode.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Challenge.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Event.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Pairing.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Session.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/TrustedDevice.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/User.php');
require(dirname(__FILE__) . '/../lib/Userbin/JWT.php');
require(dirname(__FILE__) . '/TestTransport.php');
require(dirname(__FILE__) . '/../lib/Userbin/Request.php');
