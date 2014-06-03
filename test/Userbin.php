<?php

abstract class Userbin_TestCase extends \PHPUnit_Framework_TestCase
{
  public function assertRequest($method, $url)
  {
    $request = Userbin_RequestTransport::getLastRequest();
    $this->assertTrue($request['method'] == $method && $request['url'] == Userbin_Request::apiUrl($url));
  }
}

require(dirname(__FILE__) . '/../lib/Userbin/Userbin.php');
require(dirname(__FILE__) . '/../lib/Userbin/Errors.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Resource.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Model.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/Session.php');
require(dirname(__FILE__) . '/../lib/Userbin/Models/User.php');
require(dirname(__FILE__) . '/../lib/Userbin/JWT.php');
require(dirname(__FILE__) . '/TestTransport.php');
require(dirname(__FILE__) . '/../lib/Userbin/Request.php');

?>