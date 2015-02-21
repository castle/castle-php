<?php

class Castle_Event extends RestModel
{
  public function context()
  {
    return $this->hasOne('Castle_Context');
  }
}
