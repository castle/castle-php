<?php

class Userbin_JWT
{
  function __construct($token=null)
  {
    $jwt = Array(null, null, null);
    if (isset($token)) {
      $jwt = explode(".", $token);
    }
    list($this->_header, $this->_token, $this->_signature) = $jwt;
  }

  protected function getHmac()
  {
    return hash_hmac('sha256', "$this->_header.$this->_token", Userbin::getApiKey(), true);
  }

  public function hasExpired()
  {
    $headers = $this->getHeader();
    date_default_timezone_set('UTC');
    return time() > intval($headers['exp']);
  }

  public function isValid()
  {
    $hmac = $this->getHmac();
    if (self::base64Encode($hmac) == $this->_signature) {
      return true;
    }
    else {
      throw new Userbin_SecurityError('Signature verification failed');
    }
  }

  public function getHeader($key=null)
  {
    $headers = json_decode(self::base64Decode($this->_header), true);
    if (is_string($key)) {
      if (array_key_exists($key, $headers)) {
        return $headers[$key];
      }
      else {
        return null;
      }
    }
    return $headers;
  }

  public function setHeader($key, $value=null)
  {
    $header = $this->getHeader();
    if (is_array($key)) {
      $header = $key;
    }
    else if (isset($value)) {
      if (!isset($header)) {
        $header = array();
      }
      $header[$key] = $value;
    }
    $this->_header = self::base64Encode(json_encode($header));
    return $this;
  }

  public function getBody()
  {
    return json_decode(self::base64Decode($this->_token), true);
  }

  public function toString()
  {
    $signature = $this->getHmac();
    return join('.', array($this->_header, $this->_body, $signature));
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

?>
