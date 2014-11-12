<?php

class Userbin_Pairing extends RestModel
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
