<?php

class Castle_SessionToken
{
  protected $jwt;

  public function __construct($token)
  {
    $this->jwt = new Castle_JWT($token);
  }

  public function __toString()
  {
    return $this->serialize();
  }

  public function getId()
  {
    return $this->jwt->getHeader('sub');
  }

  public function getUser()
  {
    return new Castle_User('$current');
  }

  public function hasDefaultPairing()
  {
    return $this->jwt->getBody('dpr') > 0;
  }

  public function hasExpired()
  {
    return $this->jwt->hasExpired();
  }

  public function isDeviceTrusted()
  {
    return $this->jwt->getBody('tru') == 1;
  }

  public function isMFAEnabled()
  {
    return $this->jwt->getBody('mfa') == 1;
  }

  public function isMFAInProgress()
  {
    return $this->jwt->getBody('chg') == 1;
  }

  public function isMFARequired()
  {
    return $this->jwt->getBody('vfy') > 0;
  }

  public function serialize()
  {
    return $this->jwt->toString();
  }
}