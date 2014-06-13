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

  public function user()
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

  public function getChallenge()
  {
    return null;
  }

  public function setChallenge(string $challengeId)
  {
    # code...
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