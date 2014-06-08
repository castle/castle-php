<?php

class Userbin_User extends Userbin_Model
{
  public function sessions()
  {
    return $this->hasMany('Userbin_Session', $this->sessions);
  }
}

?>