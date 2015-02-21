<?php

class Castle_User extends RestModel
{
  public function backupCodes()
  {
    return $this->hasOne('Castle_BackupCodes');
  }

  public function challenges()
  {
    return $this->hasMany('Castle_Challenge');
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
    return $this->hasMany('Castle_Event');
  }

  public function pairings()
  {
    return $this->hasMany('Castle_Pairing');
  }

  public function sessions()
  {
    return $this->hasMany('Castle_Session');
  }

  public function trustedDevices()
  {
    return $this->hasMany('Castle_TrustedDevice');
  }
}
