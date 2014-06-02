<?php

class Userbin_User extends Userbin_Model
{
  public function createSession()
  {
    $response = $this->post('/sessions', array('user' => $this->attributes));
    return new Userbin_Session($response);
  }
}

?>