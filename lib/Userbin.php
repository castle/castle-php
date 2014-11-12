<?php

if (!function_exists('curl_init')) {
  throw new Exception('Userbin needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Userbin needs the JSON PHP extension.');
}

if (!function_exists('lcfirst'))
{
  function lcfirst( $str ) {
    $str[0] = strtolower($str[0]);
    return (string)$str;
  }
}

require(dirname(__FILE__) . '/Userbin/Userbin.php');
require(dirname(__FILE__) . '/Userbin/Errors.php');
require(dirname(__FILE__) . '/Userbin/SessionToken.php');
require(dirname(__FILE__) . '/Userbin/SessionStore.php');
require(dirname(__FILE__) . '/Userbin/TrustedTokenStore.php');
require(dirname(__FILE__) . '/Userbin/Resource.php');
require(dirname(__FILE__) . '/Userbin/Model.php');
require(dirname(__FILE__) . '/Userbin/Models/Account.php');
require(dirname(__FILE__) . '/Userbin/Models/BackupCode.php');
require(dirname(__FILE__) . '/Userbin/Models/Challenge.php');
require(dirname(__FILE__) . '/Userbin/Models/Event.php');
require(dirname(__FILE__) . '/Userbin/Models/Pairing.php');
require(dirname(__FILE__) . '/Userbin/Models/Session.php');
require(dirname(__FILE__) . '/Userbin/Models/TrustedDevice.php');
require(dirname(__FILE__) . '/Userbin/Models/User.php');
require(dirname(__FILE__) . '/Userbin/JWT.php');
require(dirname(__FILE__) . '/Userbin/CurlTransport.php');
require(dirname(__FILE__) . '/Userbin/Request.php');
