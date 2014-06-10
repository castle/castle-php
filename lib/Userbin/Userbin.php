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
  public static function getSession()
  {
    if (array_key_exists('userbin', $_SESSION)) {
      $session = new Userbin_Session();
      $session->setId($_SESSION['userbin']);
      return $session;
    }
    return null;
  }

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

  public static function securitySettingsUrl()
  {
    $session = self::getSession();

    if (empty($session)) {
      throw new Userbin_Error();
    }
    return 'https://security.userbin.com/?session_token='.$session->getId();
  }
}