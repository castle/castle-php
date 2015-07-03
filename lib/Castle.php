<?php

if (!function_exists('curl_init')) {
  throw new Exception('Castle needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Castle needs the JSON PHP extension.');
}

if (!function_exists('lcfirst'))
{
  function lcfirst( $str ) {
    $str[0] = strtolower($str[0]);
    return (string)$str;
  }
}

require(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
