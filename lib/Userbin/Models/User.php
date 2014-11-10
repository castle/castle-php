<?php

class Userbin_User extends Userbin_Model
{
  public function backupCodes()
  {
    return $this->hasOne('Userbin_BackupCode');
  }

  public function challenges()
  {
    return $this->hasMany('Userbin_Challenge', $this->challenges);
  }

  public function disableMFA()
  {
    $this->post('disable_mfa');
  }

  public function enableMFA()
  {
    $this->post('enable_mfa');
  }

  public function events()
  {
    return $this->hasMany('Userbin_Event', $this->events);
  }

  public function pairings()
  {
    return $this->hasMany('Userbin_Pairing', $this->pairings);
  }

  public function sessions()
  {
    return $this->hasMany('Userbin_Session', $this->sessions);
  }

  public function trustedDevices()
  {
    return $this->hasMany('Userbin_TrustedDevice');
  }
}
