<?php

class Userbin_Challenge extends Userbin_Model
{
  public function verify($response)
  {
    $cId = $this->getId();
    if (isset($cId)) {
      $this->post('verify', array('response' => $response));
      return true;
    }
    return false;
  }
}