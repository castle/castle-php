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
      $instance->mfa_enabled = !!$this->jwt->getHeader('mfa');
    }
    return $instance;
  }

  public function clearChallenge()
  {
    $body = $this->jwt->getBody();
    if (array_key_exists('chg', $body)) {
      unset($body['chg']);
      unset($body['typ']);
      $this->jwt->setBody($body);
      $this->token = $this->jwt->toString();
    }
  }

  public function getChallenge()
  {
    $challengeId = $this->jwt->getBody('chg');
    if ($challengeId) {
      $instance = new Userbin_Challenge(array(
        'channel' => array('type' => $this->jwt->getBody('typ'))
      ));
      $instance->setId($challengeId);
      return $instance;
    }
    return null;
  }

  public function needsChallenge()
  {
    return !!$this->jwt->getHeader('vfy');
  }

  public function setChallenge(Userbin_Challenge $challenge)
  {
    $cId = $challenge->getId();
    if (isset($cId)) {
      $this->jwt->setBody('chg', $cId);
      $this->jwt->setBody('typ', $challenge->channel['type']);
    }
  }
}