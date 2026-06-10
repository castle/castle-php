<?php

namespace Castle;

class RequestContext
{
  # Extract a request context from the $Server environment.
  public static function extract() {
    return array(
      'ip' => self::extractIp(),
      'headers' => self::extractHeaders(),
      'library' => array(
        'name' => 'castle-php',
        'version' => Castle::VERSION
      )
    );
  }

  # Extract a request context from the $Server environment as JSON.
  public static function extractJSON() {
    return json_encode(self::extract());
  }

  public static function extractHeaders()
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
        // If using allowlist, only use headers *PRESENT* in it.
        // If using scrublist, only use headers *NOT* present in it.
        if (Castle::getUseAllowlist() ?
              in_array($name, Castle::$allowlistedHeaders) :
              !in_array($name, Castle::$scrubHeaders)) {
          $headers[$name] = $val;
        }
      }
    }
    return $headers;
  }

  public static function extractIp()
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
}
