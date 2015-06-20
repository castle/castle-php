<?php

class Castle_User extends RestModel
{
  public function events()
  {
    return $this->hasMany('Castle_Event');
  }

  public function pairings()
  {
    return $this->hasMany('Castle_Pairing');
  }
}
