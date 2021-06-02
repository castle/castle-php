<?php

abstract class Castle
{
  const VERSION = '3.0.0';

  const HEADER_COOKIE = 'Cookie';
  const HEADER_USER_AGENT = 'User-Agent';

  public static $apiKey;

  public static $apiBase = 'https://api.castle.io';

  public static $apiVersion = 'v1';

  public static $tokenStore = 'Castle_TokenStore';

  public static $cookieStore = 'Castle_CookieStore';

  public static $scrubHeaders = array(self::HEADER_COOKIE);

  private static $useAllowlist = false;
  public static $allowlistedHeaders = array(self::HEADER_USER_AGENT);

  private static $curlOpts = array();
  private static $validCurlOpts = array(CURLOPT_CONNECTTIMEOUT,
                                        CURLOPT_CONNECTTIMEOUT_MS,
                                        CURLOPT_TIMEOUT,
                                        CURLOPT_TIMEOUT_MS);

  public static function getApiKey()
  {
    return self::$apiKey;
  }

  public static function setApiKey($apiKey)
  {
    self::$apiKey = $apiKey;
  }

  public static function setCurlOpts($curlOpts)
  {
    $invalidOpts = array_diff(array_keys($curlOpts), self::$validCurlOpts);
    // If any options are invalid.
    if (count($invalidOpts)) {
      // Throw an exception listing all invalid options.
      throw new Castle_CurlOptionError('These cURL options are not allowed:' .
                                       join(',', $invalidOpts));
    }
    // May seem odd, but one may want the option of stripping them out, and so
    // would probably simply use error_log instead of throw.
    self::$curlOpts = array_diff($curlOpts, array_flip($invalidOpts));
  }

  public static function getCurlOpts()
  {
    return self::$curlOpts;
  }

  public static function getUseAllowlist()
  {
    return self::$useAllowlist;
  }

  public static function setUseAllowlist($use)
  {
    // Force User-Agent to be present in allowlisted if it is not.
    if ($use && !in_array(self::HEADER_USER_AGENT, self::$allowlistedHeaders)) {
      self::$allowlistedHeaders[] = self::HEADER_USER_AGENT;
    }
    self::$useAllowlist = $use;
  }

  public static function getApiVersion()
  {
    return self::$apiVersion;
  }

  public static function setApiVersion($apiVersion)
  {
    self::$apiVersion = $apiVersion;
  }

  public static function getCookieStore()
  {
    return new self::$cookieStore;
  }

  public static function getTokenStore()
  {
    return new self::$tokenStore(self::getCookieStore());
  }

  public static function setTokenStore($serializerClass)
  {
    self::$tokenStore = $serializerClass;
  }


  /**
   * Authenticate an action
   * @param  String $attributes 'user_id' and 'event' are required
   * @return Castle_Authenticate
   */
  public static function authenticate(Array $attributes)
  {
    $auth = new Castle_Authenticate($attributes);
    $auth->save();
    return $auth;
  }

  public static function impersonate($attributes) {
      $request = new Castle_Request();
      if(isset($attributes['reset'])) {
        $request->send('delete', '/impersonate', $attributes);
      } else {
        $request->send('post', '/impersonate', $attributes);
      }
  }

  /**
   * Track a security event
   * @param  Array  $attributes An array of attributes to track. The 'event' key
   *                            is required
   * @return None
   */
  public static function track(Array $attributes)
  {
    $request = new Castle_Request();
    $request->send('post', '/track', $attributes);
  }


  /**
   * Filter an action
   * @param  String $attributes 'request_token', 'event', 'context' are required, 'user' with 'id' and 'properties' are optional
   * @return Castle_Log
   */
  public static function filter(Array $attributes)
  {
    $request = new Castle_Request();
    list($response, $request) = $request->send('post', '/filter', $attributes);
    if ($request->rStatus == 204) {
      $response = array();
    }
    return new RestModel($response);
  }

  /**
   * Log events
   * @param  String $attributes 'request_token', 'event', 'status' and 'user' object with 'id' are required
   * @return Castle_Log
   */
  public static function log(Array $attributes)
  {
    $request = new Castle_Request();
    $request->send('post', '/log', $attributes);
  }

  /**
   * Risk
   * @param  String $attributes 'request_token', 'event', 'context', 'user' with 'id' are required, 'status', 'properties' are optional
   * @return Castle_Risk
   */
  public static function risk(Array $attributes)
  {
    $request = new Castle_Request();
    list($response, $request) = $request->send('post', '/risk', $attributes);
    if ($request->rStatus == 204) {
      $response = array();
    }
    return new RestModel($response);
  }
}
