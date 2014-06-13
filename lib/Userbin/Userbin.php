<?php

abstract class Userbin
{
  public static $apiKey;

  public static $apiBase = 'https://secure.userbin.com';

  public static $apiVersion = 'v1';

  public static $sessionAdapter = 'Userbin_SessionAdapter';

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

  public static function getSessionAdapter()
  {
    return new self::$sessionAdapter;
  }

  public static function setSessionAdapter($serializerClass)
  {
    self::$sessionAdapter = $serializerClass;
  }

  /*
   * Helpers
   */
  public static function getSession()
  {
    $sessionData = self::getSessionAdapter()->read();
    if ($sessionData) {
      return Userbin_Session::load($sessionData);
    }
    return null;
  }

  public static function authorize($userId, array $userData=array())
  {
    $session = self::getSession();

    if (empty($session)) {
      $user = new Userbin_User($userData);
      $user->setId($userId);
      $session = $user->sessions()->create();
      self::getSessionAdapter()->write($session->serialize());
    }
    else {
      if ($session->user()->getId() != $userId) {
        self::logout();
        throw new Userbin_Error('Session scopes not supported yet');
      }
      if ($session->hasExpired()) {
        $session->post('/synchronize', array('user' => $userData));
      }
    }

    return $session->user();
  }

  public static function logout()
  {
    $session = self::getSession();
    if (isset($session)) {
      $session->delete();
      self::getSessionAdapter()->destroy();
    }
  }

  public static function twoFactorAuthenticate()
  {
    $session = self::getSession();
    $challenge = $session->user()->challenges()->create();
    $session->setChallenge($challenge);
    self::getSessionAdapter()->write($session->serialize());
  }

  public static function twoFactorVerify($response)
  {
    $session = self::getSession();
    if (empty($session)) {
      return false;
    }
    $challenge = $session->getChallenge();
    $result = $challenge->verify($response);
    if ($result) {
      $session->clearChallenge();
      self::getSessionAdapter()->write($session->serialize());
    }
    return $result;
  }

  public static function securitySettingsUrl()
  {
    $session = self::getSessionAdapter()->read();

    if (empty($session)) {
      throw new Userbin_Error();
    }
    return 'https://security.userbin.com/?session_token='.$session;
  }
}