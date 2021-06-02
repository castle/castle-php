<?php

class Castle_RequestContext
{
  # Extract a request context from the $Server environment.
  public static function extract() {
    return array(
      'client_id' => self::extractClientId(),
      'ip' => self::extractIp(),
      'headers' => self::extractHeaders(),
      'user_agent' => self::extractUserAgent(),
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

  public static function extractUserAgent()
  {
    if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
      return $_SERVER['HTTP_USER_AGENT'];
    }
    return null;
  }

  public static function extractClientId()
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
