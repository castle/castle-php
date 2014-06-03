<?php

class Userbin_User extends Userbin_Model
{
  public function sessions()
  {
    return $this->hasResource('Userbin_Session');
  }
}

?>