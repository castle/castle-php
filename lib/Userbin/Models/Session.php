<?php

class Userbin_Session extends Userbin_Model
{

  protected $idAttribute = 'token';

  private function getJWT() {
    $jwt = new Userbin_JWT($this->getId());
    if (!$jwt->isValid()) {
      throw new Userbin_Error("JWT signature error");
    }
    return $jwt;
  }

  public function hasExpired()
  {
    $jwt = new Userbin_JWT($this->token);
    return $jwt->hasExpired();
  }

  public function refresh($attributes=null)
  {
    $response = $this->post('/refresh', array('user' => $attributes));
    $this->setAttributes($response);
    return $this;
  }

  public function serialize()
  {
    return $this->token;
  }

  public function sync($userId, $userData)
  {
    if ($this->token) {
      if ($this->hasExpired()) {
        $this->refresh($userData);
      }
    }
    else {
      $user = new Userbin_User($userData);
      $user->setId($userId);
      $session = $user->sessions()->create();
      $this->setAttributes($session->getAttributes());
    }
    return $this;
  }

  public function user()
  {
    $userId = $this->getJWT()->getHeader('iss');
    $instance = null;
    if ($userId) {
      $instance = new Userbin_User();
      $instance->setId($userId);
    }
    return $instance;
  }

  public static function load($jwtString)
  {
    $instance = new static;
    $instance->setId($jwtString);
    $jwt = $instance->getJWT(); // Implicit validate
    return $instance;
  }
}