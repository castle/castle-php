<?php

abstract class Userbin_TestCase extends \PHPUnit_Framework_TestCase
{
  public function assertRequest($method, $url, $headers=null)
  {
    $request = Userbin_RequestTransport::getLastRequest();
    $this->assertTrue($request['method'] == $method && $request['url'] == Userbin_Request::apiUrl($url));
    if (is_array($headers)) {
      foreach ($headers as $key => $value) {
        $this->assertTrue(array_key_exists($key, $request['headers']));
        $this->assertEquals($request['headers'][$key], $value);
      }
    }
  }
}

require(dirname(__FILE__) . '/../lib/Userbin/Userbin.php');
require(dirname(__FILE__) . '/../lib/Userbin/Errors.php');
require(dirname(__FILE__) . '/../lib/Userbin/SessionAdapter.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Resource.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Model.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Challenge.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Session.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/User.php');
require(dirname(__FILE__) . '/../lib/Userbin/JWT.php');
require(dirname(__FILE__) . '/TestTransport.php');
require(dirname(__FILE__) . '/../lib/Userbin/Request.php');

?>