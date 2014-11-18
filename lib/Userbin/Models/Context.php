<?php

class Userbin_Context extends RestModel
{
  public function location()
  {
    return $this->hasOne('RestModel', 'location');
  }

  public function userAgent()
  {
    return $this->hasOne('RestModel', 'user_agent');
  }
}
