<?php

abstract class Userbin
{
  public static $apiKey;

  public static $apiBase = 'https://secure.userbin.com';

  public static $apiVersion = 'v1';

  public static $sessionSerializer = 'Userbin_SessionSerializer';

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

  public function getSerializer()
  {
    return new self::$sessionSerializer;
  }

  public function setSerializer($serializerClass)
  {
    self::$sessionSerializer = $serializerClass;
  }

  /*
   * Helpers
   */
  public static function getSession()
  {
    $sessionData = self::getSerializer()->read();
    if ($sessionData) {
      return Userbin_Session::load($sessionData);
    }
    return null;
  }

  public static function authorize($userId, array $userData=array())
  {
    $session = self::getSession();
    if (empty($session)) {
      $session = new Userbin_Session();
    }
    $session->sync($userId, $userData);

    self::getSerializer()->write($session->serialize());
    return $session;
  }

  public static function logout()
  {
    $session = self::getSession();
    if (isset($session)) {
      $session->delete();
      self::getSerializer()->destroy();
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