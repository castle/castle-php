<?php

class Userbin_RequestTransport
{
  public $rBody;
  public $rHeaders;
  public $rStatus;
  public $rError;
  public $rMessage;

  private static $params = null;

  private static $lastRequest = null;

  public function send($method, $url, $params=null, $headers=array()) {
    if (!self::$params) {
      self::setResponse(200, '{}');
    }
    $headers_array = array();
    foreach ($headers as $header) {
      preg_match('#(.*?)\:\s(.*)#', $header, $matches);
      if (!empty($matches[1])) {
        $headers_array[$matches[1]] = $matches[2];
      }
    }
    self::$lastRequest = array(
      'method'  => $method,
      'headers' => $headers_array,
      'params'  => $params,
      'url'     => $url
    );
    $this->rBody = self::$params['body'];
    $this->rStatus = self::$params['code'];
    $this->rHeaders = self::$params['headers'];
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

  public static function setResponse($code=200, $body='', $headers=array()) {
    if (is_array($body)) {
      $body = json_encode($body, true);
    }
    self::$params = array(
      'body' => $body,
      'code' => $code,
      'headers' => $headers
    );
  }
}
