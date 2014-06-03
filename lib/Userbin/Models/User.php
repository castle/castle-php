<?php

class Userbin_User extends Userbin_Model
{
  public function createSession()
  {
    $response = $this->post('/sessions', array('user' => $this->attributes));
    if (!is_array($response)) {
      throw new Userbin_Error('The returned session was invalid');
    }
    return new Userbin_Session($response);
  }
}

?>