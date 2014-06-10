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
  public static function startSession($userId, array $userData=array())
  {
    $session = new Userbin_Session();
    if (array_key_exists('userbin', $_SESSION)) {
      $session->setId($_SESSION['userbin']);
    }

    $session->sync($userId, $userData);
    $_SESSION['userbin'] = $session->serialize();
    return $session;
  }

  public static function destroySession()
  {
    if (array_key_exists('userbin', $_SESSION)) {
      Userbin_Session::destroy($_SESSION['userbin']);
      unset($_SESSION['userbin']);
    }
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