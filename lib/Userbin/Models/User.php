<?php

class Userbin_User extends Userbin_Model
{
  public function challenges()
  {
    return $this->hasMany('Userbin_Challenge', $this->sessions);
  }

  public function sessions()
  {
    return $this->hasMany('Userbin_Session', $this->sessions);
  }
}
