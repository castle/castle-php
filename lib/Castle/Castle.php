<?php

abstract class Castle
{
  public static $apiKey;

  public static $apiBase = 'https://api.castle.io';

  public static $apiVersion = 'v1';

  public static $tokenStore = 'Castle_TokenStore';

  public static $cookieStore = 'Castle_CookieStore';

  public static $scrubHeaders = array('Cookie');

  const VERSION = '1.2.3';

  public static function getApiKey()
  {
    return self::$apiKey;
  }

  public static function setApiKey($apiKey)
  {
    self::$apiKey = $apiKey;
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
   * Get the current risk score for a user
   * @param  String $user_id  Id of the user
   * @return None
   */
  public static function authenticate($user_id)
  {
    $auth = new Castle_Authentication(Array('user_id' => $user_id));
    $auth->save();
  }

  public static function authentications($id = null)
  {
    $auth = new Castle_Authentication();
    if (isset($id)) {
      $auth->setId($id);
    }
    return $auth;
  }

  /**
   * Updates user information. Call when a user logs in or updates their information.
   * @param  String $user_id  Id of the user
   * @param  Array  $attributes Additional user properties
   * @return  None
   */
  public static function identify($user_id, Array $attributes)
  {
    $user = new Castle_User($user_id);
    $user->setAttributes($attributes);
    $user->save();
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
    $request->send('post', '/events', $attributes);
  }
}
