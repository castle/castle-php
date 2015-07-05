<?php

namespace Castle\Models;

class Event extends RestModel
{
  public function context()
  {
    return $this->hasOne('Castle_Context');
  }
}
