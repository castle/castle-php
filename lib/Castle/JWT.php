<?php

namespace Castle;

use Castle\Errors\SecurityError;

class JWT
{
  function __construct($token=null)
  {
    $jwt = Array('', '', '');
    list($this->_header, $this->_body, $this->_signature) = $jwt;
    if (is_string($token)) {
      $jwt = explode(".", $token);
      if (count($jwt) != 3) {
        throw new SecurityError('Invalid JWT');
      }
      list($this->_header, $this->_body, $this->_signature) = $jwt;
      $this->isValid();
    }
  }

  protected function getHmac()
  {
    return hash_hmac('sha256', "$this->_header.$this->_body", Castle::getApiKey(), true);
  }

  public function getArrayKey($array, $key=null)
  {
    if (!is_array($array)) {
      $array = array();
    }
    if (is_string($key)) {
      if (array_key_exists($key, $array)) {
        return $array[$key];
      }
      else {
        return null;
      }
    }
    return $array;
  }

  public function setArrayKey($array, $key, $value=null)
  {
    if (!is_array($array)) {
      $array = array();
    }
    if (is_array($key)) {
      $array = $key;
    }
    else if (isset($value)) {
      $array[$key] = $value;
    }
    else {
      unset($array[$key]);
    }
    return $array;
  }

  public function hasExpired()
  {
    $headers = $this->getHeader();
    if (!array_key_exists('exp', $headers)) {
      throw new SecurityError('Invalid JWT. Has no expiry time');
    }
    date_default_timezone_set('UTC');
    return time() > intval($headers['exp']);
  }

  public function isValid()
  {
    $jwt = $this->_header.'.';
    $jwt .= $this->_body.'.';
    $jwt .= $this->_signature;
    $hmac = $this->calculateSignature();
    if ($hmac == $this->getSignature()) {
      return true;
    }
    else {
      throw new SecurityError('Signature verification failed');
    }
  }

  public function getHeader($key=null)
  {
    $headers = json_decode(self::base64Decode($this->_header), true);
    return $this->getArrayKey($headers, $key);
  }

  public function setHeader($key, $value=null)
  {
    $headers = $this->setArrayKey($this->getHeader(), $key, $value);
    $this->_header = self::base64Encode(json_encode($headers));
    $this->calculateSignature(true);
    return $this;
  }

  public function getBody($key=null)
  {
    $body = json_decode(self::base64Decode($this->_body), true);
    return $this->getArrayKey($body, $key);
  }

  public function setBody($key, $value=null)
  {
    $body = $this->setArrayKey($this->getBody(), $key, $value);
    $this->_body = self::base64Encode(json_encode($body));
    $this->calculateSignature(true);
    return $this;
  }

  public function calculateSignature($update=false)
  {
    $signature = self::base64Encode($this->getHmac());
    if ($update) {
      $this->setSignature($signature);
    }
    return $signature;
  }

  public function getSignature()
  {
    return $this->_signature;
  }

  public function setSignature($signature)
  {
    $this->_signature = $signature;
  }

  public function toString()
  {
    return join('.', array($this->_header, $this->_body, $this->_signature));
  }

  public static function base64Encode($data)
  {
    return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
  }

  public static function base64Decode($data)
  {
    $rem = strlen($data) % 4;
    if ($rem) {
      $pad = 4 - $rem;
      $data .= str_repeat('=', $pad);
    }
    return base64_decode(strtr($data, '-_', '+/'));
  }
}
