<?php

class Userbin_Event extends RestModel
{
  public function context()
  {
    return $this->hasOne('Userbin_Context');
  }
}
