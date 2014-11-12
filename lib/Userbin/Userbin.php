<?php

abstract class Userbin
{
  public static $apiKey;

  public static $apiBase = 'https://api.userbin.com';

  public static $apiVersion = 'v1';

  public static $sessionStore = 'Userbin_SessionStore';

  public static $trustedTokenStore = 'Userbin_TrustedTokenStore';

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

  public static function getSessionStore()
  {
    return new self::$sessionStore;
  }

  public static function setSessionStore($serializerClass)
  {
    self::$sessionStore = $serializerClass;
  }

  public static function getSessionToken()
  {
    $sessionData = self::getSessionStore()->read();
    if ($sessionData) {
      return new Userbin_SessionToken($sessionData);
    }
    return null;
  }

  public static function setSessionToken($token)
  {
    if ( is_string($token) ) {
      Userbin::getSessionStore()->write($token);
    }
    else {
      Userbin::getSessionStore()->destroy();
    }
  }

  public static function getTrustedTokenStore($value='')
  {
    return new self::$trustedTokenStore;
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
      throw new Userbin_UserUnauthorizedError('Need to call login before authorize');
    }

    if ( $sessionToken->hasExpired() ) {
      $request = new Userbin_Request();
      $request->send('post', '/heartbeat');
    }

    if ( self::isMFAInProgress() ) {
      self::logout();
      throw new Userbin_UserUnauthorizedError('Logged out due to being unverified');
    }

    if ( self::isMFARequired() && !self::isDeviceTrusted() ) {
      throw new Userbin_ChallengeRequiredError('Two-step verification necessary');
    }
  }

  /**
   * Returns the currently logged in user, if any
   * @return Userbin_User User object, null if not available
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
   * Sets the currently logged in user id
   * @param  String $userId The local ID of your currently logged in user
   * @return None
   */
  public static function identify($userId)
  {
    $userId = urlencode($userId);
    Userbin::getSessionStore()->setUserId($userId);
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
    return $sessionToken ? $sessionToken->hasChallenge() : false;
  }

  public static function isMFARequired()
  {
    $sessionToken = self::getSessionToken();
    return $sessionToken ? $sessionToken->needsChallenge() : false;
  }


  /**
   * Creates a session for a user using
   * @param  String $userId         Local database ID
   * @param  Array  $userAttributes Optional user attributes
   * @return Userbin_SessionToken   The created session
   */
  public static function login($userId, $userAttributes = Array())
  {
    self::setSessionToken(null);

    self::identify($userId);

    $user = new Userbin_User($userAttributes);
    $user->setId($userId);
    if ( is_array($userAttributes) ) {
      $userAttributes = array('user' => $userAttributes);
    }
    $newSession = $user->sessions()->create($userAttributes);
    $session = new Userbin_SessionToken($newSession->token);
    self::setSessionToken($session->serialize());
    return $user;
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
      $session = new Userbin_Session($sessionToken->getId());
      $session->delete();
    } catch (Userbin_ApiError $e) {}

    self::setSessionToken(null);
  }

  /**
   * Creates a trusted device for the current user and saves the id in the
   * selected store (default Cookies via `setcookie`)
   * @return Userbin_TrustedDevice The created trusted device object. Null if not available
   */
  public static function trustDevice($attributes = null)
  {
    $currentUser = self::currentUser();
    if (!$currentUser) {
      return null;
    }
    $trustedDevice = self::currentUser()->trustedDevices()->create($attributes);

    self::getTrustedTokenStore()->write($trustedDevice->id);
    return $trustedDevice;
  }

  /**
   * Returns the id of the trusted device, if any
   * @return String Id string if available, null otherwise
   */
  public static function trustedDeviceToken()
  {
    return self::getTrustedTokenStore()->read();
  }
}
