<?php

class Userbin_SessionToken
{
  protected $jwt;

  public function __construct($token)
  {
    $this->jwt = new Userbin_JWT($token);
  }

  public function hasExpired()
  {
    return $this->jwt->hasExpired();
  }

  public function serialize()
  {
    return $this->jwt->toString();
  }

  public function getId()
  {
    return $this->jwt->getHeader('sub');
  }

  public function getUser()
  {
    $userId = $this->jwt->getHeader('iss');
    $instance = null;
    if ($userId) {
      $instance = new Userbin_User();
      $instance->setId($userId);
    }
    return $instance;
  }

  public function getChallenge()
  {
    $challenge = $this->jwt->getBody('chg');
    if (is_array($challenge)) {
      $instance = new Userbin_Challenge(array(
        'channel' => array('type' => $challenge['typ'])
      ));
      $instance->setId($challenge['id']);
      return $instance;
    }
    return null;
  }

  public function needsChallenge()
  {
    return !!$this->jwt->getBody('vfy');
  }
}