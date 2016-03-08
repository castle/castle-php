<?php

abstract class Castle_TestCase extends \PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    Castle::setApiKey('secret');
  }

  public function assertRequest($method, $url, $headers=null)
  {
    $request = Castle_RequestTransport::getLastRequest();
    $this->assertEquals($method, $request['method']);
    $this->assertEquals(Castle_Request::apiUrl($url), $request['url']);
    if (is_array($headers)) {
      foreach ($headers as $key => $value) {
        $this->assertArrayHasKey($key, $request['headers']);
        $this->assertEquals($request['headers'][$key], $value);
      }
    }
    return $request;
  }
}

require(dirname(__FILE__) . '/../lib/Castle/Castle.php');
require(dirname(__FILE__) . '/../lib/Castle/Errors.php');
require(dirname(__FILE__) . '/../lib/Castle/SessionToken.php');
require(dirname(__FILE__) . '/../lib/Castle/TokenStore.php');
require(dirname(__FILE__) . '/CookieStore.php');
require(dirname(__FILE__) . '/../lib/RestModel/Resource.php');
require(dirname(__FILE__) . '/../lib/RestModel/Model.php');
require(dirname(__FILE__) . '/../lib/Castle/Models/Account.php');
require(dirname(__FILE__) . '/../lib/Castle/Models/Authentication.php');
require(dirname(__FILE__) . '/../lib/Castle/Models/Context.php');
require(dirname(__FILE__) . '/../lib/Castle/Models/Event.php');
require(dirname(__FILE__) . '/../lib/Castle/Models/Label.php');
require(dirname(__FILE__) . '/../lib/Castle/Models/User.php');
require(dirname(__FILE__) . '/../lib/Castle/JWT.php');
require(dirname(__FILE__) . '/TestTransport.php');
require(dirname(__FILE__) . '/../lib/Castle/Request.php');
