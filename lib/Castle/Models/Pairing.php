<?php

class Castle_Pairing extends RestModel
{
  public function setDefault()
  {
    return $this->post('set_default');
  }

  public function verify($params)
  {
    return $this->post('verify', $params);
  }
}
