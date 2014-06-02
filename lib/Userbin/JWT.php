<?php

class Userbin_JWT
{
  function __construct($token)
  {
    $jwt = Array(null, null, null);
    if ($token) {
      $jwt = explode(".", $token);
    }
    list($this->_header, $this->_token, $this->_signature) = $jwt;
  }

  public function hasExpired()
  {
    $headers = $this->getHeader();
    date_default_timezone_set('UTC');
    return time() > intval($headers['exp']);
  }

  public function isValid()
  {
    $hmac = hash_hmac('sha256', "$this->_header.$this->_token", Userbin::getApiKey(), true);
    if (self::base64Encode($hmac) == $this->_signature) {
      return true;
    }
    else {
      throw new Userbin_SecurityError('Signature verification failed');
    }
  }

  public function getHeader()
  {
    return json_decode(self::base64Decode($this->_header), true);
  }

  public function getBody()
  {
    return json_decode(self::base64Decode($this->_token), true);
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
