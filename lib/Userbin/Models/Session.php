<?php

class Userbin_Session extends Userbin_Model
{

  private function getJWT() {
    $jwt = new Userbin_JWT($this->token);
    $valid = $jwt->isValid();
    return $jwt;
  }

  public function hasExpired()
  {
    return $this->getJWT()->hasExpired();
  }

  public function serialize()
  {
    return $this->token;
  }

  public function getUser()
  {
    $userId = $this->getJWT()->getHeader('iss');
    $instance = null;
    if ($userId) {
      $instance = new Userbin_User($this->user);
      $instance->setId($userId);
      $instance->mfa_enabled = !!$this->getJWT()->getHeader('mfa');
    }
    return $instance;
  }

  public function clearChallenge()
  {
    $jwt = $this->getJWT();
    $body = $jwt->getBody();
    if (array_key_exists('chg', $body)) {
      unset($body['chg']);
      $jwt->setBody($body);
      $this->token = $jwt->toString();
    }
  }

  public function getChallenge()
  {
    $challengeId = $this->getJWT()->getBody('chg');
    if ($challengeId) {
      $instance = new Userbin_Challenge();
      $instance->setId($challengeId);
      return $instance;
    }
    return null;
  }

  public function setChallenge(Userbin_Challenge $challenge)
  {
    $cId = $challenge->getId();
    if (isset($cId)) {
      $jwt = $this->getJWT();
      $jwt->setBody('chg', $cId);
      $this->token = $jwt->toString();
    }
  }

  public static function load($jwtString)
  {
    $instance = new static;
    $instance->token = $jwtString;
    $jwt = $instance->getJWT();
    $instance->setId($jwt->getHeader('sub'));
    return $instance;
  }
}
