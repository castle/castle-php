<?php

namespace Castle;

class Context extends RestModel
{
  public function location()
  {
    return $this->hasOne(RestModel::class, 'location');
  }

  public function userAgent()
  {
    return $this->hasOne(RestModel::class, 'user_agent');
  }
}
