<?php

class Castle_RequestContext
{
  public $clientId;

  public $headers;

  public $ip;

  public $body;

  public function toRequestHeaders() {
    $client = self::clientUserAgent();
    return array(
      'X-Castle-Client-Id: ' . $this->clientId,
      'X-Castle-Headers: ' . $this->headers,
      'X-Castle-Ip: ' . $this->ip,
      'X-Castle-Client-User-Agent: ' . $client,
      'Content-Type: application/json',
      'Content-Length: ' . strlen($this->body)
    );
  }

  # Instantiate a request context from an associative array.
  public static function build(array $properties) {
    $instance = new self();
    foreach ($properties as $key => $value){
      if ( property_exists ( $instance , $key ) ){
        $instance->$key = $value;
      }
    }
    return $instance;
  }

  public static function clientUserAgent()
  {
    $langVersion = phpversion();
    $uname = php_uname();
    $userAgent = array(
      'bindings_version' => Castle::VERSION,
      'lang' => 'php',
      'lang_version' => $langVersion,
      'platform' => PHP_OS,
      'publisher' => 'castle',
      'uname' => $uname
    );
    return json_encode($userAgent);
  }

  // Build a request context automatically from PHP globals
  public static function extract($params)
  {
    $contextData = self::extractArray($params);
    return self::build($contextData);
  }

  // Build an array with data necessary to create a request context
  public static function extractArray($params)
  {
    $requestHeaders = json_encode(self::getHeaders());
    $body = empty($params) ? null : json_encode($params);
    return array(
      'clientId' => self::getClientId(),
      'ip' => self::getIp(),
      'headers' => $requestHeaders,
      'body' => $body
    );
  }

  // Extract request context data and return the JSON serialized version of it
  public static function extractJson($params) {
    $contextData = self::extractArray($params);
    return json_encode($contextData);
  }

  // Build an instance of the request context based on serialized JSON data
  public static function fromJson($json)
  {
    $contextData = json_decode($json, true);
    return self::build($contextData);
  }

  public static function getHeaders()
  {
    $headers = array();
    foreach ($_SERVER as $key => $val) {
      // Find all HTTP_ headers, convert '_' to '-' and uppercase every word
      if (substr($key, 0, 5) == 'HTTP_') {
        $name = strtolower(substr($key, 5));
        if (strpos($name, '_') != -1) {
          $name = preg_replace('/ /', '-', ucwords(preg_replace('/_/', ' ', $name)));
        } else {
          $name = ucfirst($name);
        }
        // If using whitelist, only use headers *PRESENT* in it.
        // If using scrublist, only use headers *NOT* present in it.
        if (Castle::getUseWhitelist() ?
              in_array($name, Castle::$whitelistHeaders) :
              !in_array($name, Castle::$scrubHeaders)) {
          $headers[$name] = $val;
        }
      }
    }
    return $headers;
  }

  public static function getIp()
  {
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
      $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
      return $parts[0];
    }
    if (array_key_exists('HTTP_X_REAL_IP', $_SERVER)) {
      return $_SERVER['HTTP_X_REAL_IP'];
    }
    if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
      return $_SERVER['REMOTE_ADDR'];
    }
    return null;
  }

  public static function getUserAgent()
  {
    if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
      return $_SERVER['HTTP_USER_AGENT'];
    }
    return null;
  }

  public static function getClientId()
  {
    if (array_key_exists('HTTP_X_CASTLE_CLIENT_ID', $_SERVER)) {
      return self::normalize($_SERVER['HTTP_X_CASTLE_CLIENT_ID']);
    } else if (Castle::getCookieStore()->hasKey('__cid')) {
      return self::normalize(Castle::getCookieStore()->read('__cid'));
    } else {
      // If the client_id is neither send in the header nor cookie
      // we'll return the special value '?'. This doesn't have any effect on
      // functionality. This is to prevent curl from removing empty headers
      return '?';
    }
  }

  public static function normalize($cid)
  {
    $cid = preg_replace("/[[:cntrl:][:space:]]/", '', $cid);

    // If we end up with an empty/invalid cid, we'll set it to the special
    // value '_' to indicate there was a value but it was not valid.
    // This is to prevent curl from removing empty headers
    return empty($cid) ? '_' : $cid;
  }
}
