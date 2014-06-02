<?php

class Userbin_Session extends Userbin_Model
{

  protected $primaryKey = 'token';

  public function hasExpired()
  {
    $jwt = new Userbin_JWT($this->token);
    return $jwt->hasExpired();
  }

  public function refresh($attributes=null)
  {
    $response = $this->post('/refresh', array('user' => $attributes));
    $this->setAttributes($response);
    return $this;
  }
}