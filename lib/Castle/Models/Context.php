<?php

class Castle_Context extends RestModel
{
  public function library()
  {
    return $this->hasOne('RestModel', 'library');
  }
}
