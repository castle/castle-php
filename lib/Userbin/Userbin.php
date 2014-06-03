<?php

abstract class Userbin
{
  public static $apiKey;

  public static $apiBase = 'https://secure.userbin.com';

  public static $apiVersion = 'v1';

  const VERSION = '1.0.0';

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

  /*
   * Helpers
   */
  public static function authenticate($sessionToken, $userId, array $userData=array())
  {
    $session = new Userbin_Session(array('token' => $sessionToken));
    if ($session->token) {
      if ($session->hasExpired()) {
        $session->refresh();
      }
    }
    else {
      $user = new Userbin_User($userData);
      $user->id = $userId;
      $session = $user->sessions()->create();
    }

    return $session->token;
  }

  public static function deauthenticate($sessionToken)
  {
    if (!isset($sessionToken)) return;
    Userbin_Session::destroy($sessionToken);
  }

  public static function twoFactorAuthenticate()
  {

  }

  public static function securitySettingsUrl($sessionToken)
  {
    if (!isset($sessionToken)) return '';
    $jwt = new Userbin_JWT($sessionToken);

    try {
      $jwt->isValid();
    }
    catch (Exception $e) {
      return '';
    }

    $body = $jwt->getBody();
    if (array_key_exists('app_id', $body)) {
      return 'https://security.userbin.com/?session_token='.$body['app_ip'];
    }
    return '';
  }
}