<?php

abstract class Castle
{
  public static $apiKey;

  public static $apiBase = 'https://api.castle.io';

  public static $apiVersion = 'v1';

  public static $caCerts = '../certs/ca-certs.crt';

  public static $tokenStore = 'Castle_TokenStore';

  public static $cookieStore = 'Castle_CookieStore';

  public static $scrubHeaders = array('Cookie');

  const VERSION = '1.1.0';

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

  public static function getSessionToken()
  {
    $sessionData = self::getTokenStore()->getSession();
    if ($sessionData) {
      return new Castle_SessionToken($sessionData);
    }
    return null;
  }

  public static function setSessionToken($token)
  {
    if ( is_string($token) ) {
      Castle::getTokenStore()->setSession($token);
    }
    else {
      Castle::getTokenStore()->setSession();
    }
  }

  /**
   * Authorize current user. Should be called on every request the user makes
   * to verify that the session is till valid.
   * Note: A new HTTP request is only made when the current session has expired.
   * @return None
   */
  public static function authorize()
  {
    $sessionToken = self::getSessionToken();
    if ( !$sessionToken ) {
      throw new Castle_UserUnauthorizedError('Need to call login before authorize');
    }

    if ( $sessionToken->hasExpired() ) {
      $request = new Castle_Request();
      $request->send('post', '/heartbeat');
    }

    if ( self::isMFAInProgress() ) {
      self::logout();
      throw new Castle_UserUnauthorizedError('Logged out due to being unverified');
    }

    if ( self::isMFARequired() && !self::isDeviceTrusted() ) {
      throw new Castle_ChallengeRequiredError('Two-step verification necessary');
    }
  }

  /**
   * Returns the currently logged in user, if any
   * @return Castle_User User object, null if not available
   */
  public static function currentUser()
  {
    $sessionToken = self::getSessionToken();
    if (!$sessionToken) {
      return null;
    }
    return $sessionToken->getUser();
  }

  public static function hasDefaultPairing()
  {
    $sessionToken = self::getSessionToken();
    return $sessionToken ? $sessionToken->hasDefaultPairing() : false;
  }

  /**
   * Checks whether there is an active session, ie. if the current user is
   * authorized
   * @return boolean True if authorized, False otherwise
   */
  public static function isAuthorized()
  {
    return !!self::getSessionToken();
  }

  public static function isDeviceTrusted()
  {
    $sessionToken = self::getSessionToken();
    return $sessionToken ? $sessionToken->isDeviceTrusted() : false;
  }

  public static function isMFAEnabled()
  {
    $sessionToken = self::getSessionToken();
    return $sessionToken ? $sessionToken->isMFAEnabled() : false;
  }

  public static function isMFAInProgress()
  {
    $sessionToken = self::getSessionToken();
    return $sessionToken ? $sessionToken->isMFAInProgress() : false;
  }

  public static function isMFARequired()
  {
    $sessionToken = self::getSessionToken();
    return $sessionToken ? $sessionToken->isMFARequired() : false;
  }


  /**
   * Creates a session for a user using
   * @param  String $userId         Local database ID
   * @param  Array  $userAttributes Optional user attributes
   * @return Castle_SessionToken   The created session
   */
  public static function login($userId, $userAttributes = Array())
  {
    self::setSessionToken(null);

    $user = new Castle_User($userAttributes);
    $user->setId($userId);

    $newSession = $user->sessions()->create(array(
      'user' => $userAttributes,
      'trusted_device_token' => self::trustedDeviceToken()
    ));
    $session = new Castle_SessionToken($newSession->token);
    self::setSessionToken($session->serialize());
    return $session;
  }

  /**
   * Ends the currently started session. Should be called whenever the user
   * logs out from your system.
   * @return None
   */
  public static function logout()
  {
    $sessionToken = self::getSessionToken();
    if ( !$sessionToken ) {
      return false;
    }

    try {
      /* Silence cases where the session has already been removed etc. */
      $sessionId = $sessionToken->getId();
      $sessionToken->getUser()->sessions()->delete($sessionId);
    } catch (Castle_ApiError $e) {}

    self::setSessionToken(null);
  }

  public static function recommend($params = null)
  {
    $recommendation = new Castle_Recommendation();
    return $recommendation->fetch('?'.http_build_query($params));
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

  /**
   * Creates a trusted device for the current user and saves the id in the
   * selected store (default Cookies via `setcookie`)
   * @return Castle_TrustedDevice The created trusted device object. Null if not available
   */
  public static function trustDevice($attributes = null)
  {
    $currentUser = self::currentUser();
    if (!$currentUser) {
      throw new Castle_UserUnauthorizedError('Need to call login before trusting device');
    }
    $trustedDevice = self::currentUser()->trustedDevices()->create($attributes);

    self::getTokenStore()->setTrustedDevice($trustedDevice->token);
    return $trustedDevice;
  }

  /**
   * Returns the id of the trusted device, if any
   * @return String Id string if available, null otherwise
   */
  public static function trustedDeviceToken()
  {
    return self::getTokenStore()->getTrustedDevice();
  }
}
