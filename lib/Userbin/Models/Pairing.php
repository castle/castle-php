<?php

class Userbin_Pairing extends Userbin_Model
{
  public function verify($params)
  {
    $this->post('verify', $params);
  }

  public function setDefault()
  {
    $this->post('set_default');
  }
}
