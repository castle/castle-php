<?php

class Castle_Challenge extends RestModel
{
  public function pairing()
  {
    return $this->belongsTo('Castle_Pairing');
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