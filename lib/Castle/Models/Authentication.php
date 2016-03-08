<?php

class Castle_Authentication extends RestModel
{
  public function approve()
  {
    $this->post('approve');
    return $this;
  }

  public function context()
  {
    $this->hasOne('Castle_Context');
  }

  public function deny()
  {
    $this->post('deny');
    return $this;
  }

  public function reset()
  {
    $this->post('reset');
    return $this;
  }
}
