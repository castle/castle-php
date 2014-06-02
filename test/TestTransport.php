<?php

class Userbin_RequestTransport
{
  public $rBody;
  public $rStatus;
  public $rError;
  public $rMessage;

  private static $params = null;

  private static $lastRequest = null;

  public function send($method, $url, $params=null, $headers=array()) {
    if (!self::$params) {
      self::setResponse(200, '{}');
    }
    self::$lastRequest = array(
      'method'  => $method,
      'headers' => $headers,
      'params'  => $params,
      'url'     => $url
    );
    $this->rBody = self::$params['body'];
    $this->rStatus = self::$params['code'];
  }

  public static function getLastRequest()
  {
    return self::$lastRequest;
  }

  public static function reset()
  {
    self::$lastRequest = null;
    self::$params = null;
  }

  public static function setResponse($code=200, $body='') {
    if (is_array($body)) {
      $body = json_encode($body, true);
    }
    self::$params = array(
      'body' => $body,
      'code' => $code
    );
  }
}