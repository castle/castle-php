<?php

class Userbin_SessionToken
{
  protected $jwt;

  public function __construct($token)
  {
    $this->jwt = new Userbin_JWT($token);
  }

  public function getId()
  {
    return $this->jwt->getHeader('sub');
  }

  public function getUser()
  {
    return new Userbin_User('$current');
  }

  public function hasChallenge()
  {
    return $this->jwt->getBody('chg') == 1;
  }

  public function needsChallenge()
  {
    return $this->jwt->getBody('vfy') > 0;
  }

  public function isDeviceTrusted()
  {
    return $this->jwt->getBody('tru') == 1;
  }

  public function isMFAEnabled()
  {
    return $this->jwt->getBody('mfa') == 1;
  }

  public function hasDefaultPairing()
  {
    return $this->jwt->getBody('dpr') == 1;
  }

  public function hasExpired()
  {
    return $this->jwt->hasExpired();
  }

  public function serialize()
  {
    return $this->jwt->toString();
  }
}