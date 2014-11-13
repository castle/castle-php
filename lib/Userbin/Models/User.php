<?php

class Userbin_User extends RestModel
{
  public function backupCodes()
  {
    return $this->hasOne('Userbin_BackupCode');
  }

  public function challenges()
  {
    return $this->hasMany('Userbin_Challenge');
  }

  public function disableMFA($params = null)
  {
    $this->post('disable_mfa', $params);
  }

  public function enableMFA()
  {
    $this->post('enable_mfa');
  }

  public function events()
  {
    return $this->hasMany('Userbin_Event');
  }

  public function pairings()
  {
    return $this->hasMany('Userbin_Pairing');
  }

  public function sessions()
  {
    return $this->hasMany('Userbin_Session');
  }

  public function trustedDevices()
  {
    return $this->hasMany('Userbin_TrustedDevice');
  }
}
