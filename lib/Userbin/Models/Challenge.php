<?php

class Userbin_Challenge extends RestModel
{
  public function pairing()
  {
    return $this->belongsTo('Userbin_Pairing');
  }

  public function verify($response)
  {
    $cId = $this->getId();
    if (isset($cId)) {
      return $this->post('verify', array('response' => $response));
    }
    return false;
  }
}