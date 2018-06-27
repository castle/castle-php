<?php

abstract class Castle
{
  const VERSION = '1.7.0';

  const HEADER_COOKIE = 'Cookie';
  const HEADER_USER_AGENT = 'User-Agent';

  public static $apiKey;

  public static $apiBase = 'https://api.castle.io';

  public static $apiVersion = 'v1';

  public static $tokenStore = 'Castle_TokenStore';

  public static $cookieStore = 'Castle_CookieStore';

  public static $scrubHeaders = array(self::HEADER_COOKIE);

  private static $useWhitelist = false;
  public static $whitelistHeaders = array(self::HEADER_USER_AGENT);

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

  public static function getUseWhitelist()
  {
    return self::$useWhitelist;
  }

  public static function setUseWhitelist($use)
  {
    // Force User-Agent to be present in whitelist if it is not.
    if ($use && !in_array(self::HEADER_USER_AGENT, self::$whitelistHeaders)) {
      self::$whitelistHeaders[] = self::HEADER_USER_AGENT;
    }
    self::$useWhitelist = $use;
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
   * @param  String $attributes 'user_id' and 'name' are required
   * @return Castle_Authenticate
   */
  public static function authenticate(Array $attributes)
  {
    $auth = new Castle_Authenticate($attributes);
    $auth->save();
    return $auth;
  }

  /**
   * Authenticate an action
   * @param  String $attributes 'user_id' and 'name' are required
   * @return Castle_Authenticate
   */
  public static function fetchReview($id)
  {
    return Castle_Review::find($id);
  }

   /**
   * Updates user information. Call when a user logs in or updates their information.
   * @param  String $user_id  Id of the user
   * @param  Array  $traits   Additional user properties
   * @return  None
   */
  public static function identify($attributes) {
    if(func_num_args() == 1) {
      $request = new Castle_Request();
      $request->send('post', '/identify', $attributes);
    } else {
      call_user_func_array('self::legacyIdentify', func_get_args());
    }
  }

  public static function impersonate($attributes) {
      $request = new Castle_Request();
      if(isset($attributes['reset'])) {
        $request->send('delete', '/impersonate', $attributes);
      } else {
        $request->send('post', '/impersonate', $attributes);
      }
  }

  private static function legacyIdentify($user_id, Array $traits) {
    $request = new Castle_Request();
    $request->send('post', '/identify', Array(
      'user_id' => $user_id,
      'traits' => $traits
    ));
  }
  /**
   * Track a security event
   * @param  Array  $attributes An array of attributes to track. The 'name' key
   *                            is required
   * @return None
   */
  public static function track(Array $attributes)
  {
    $request = new Castle_Request();
    $request->send('post', '/track', $attributes);
  }
}
