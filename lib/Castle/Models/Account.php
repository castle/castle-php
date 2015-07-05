<?php

namespace Castle\Models;

class Account extends RestModel
{
  protected $isSingular = true;

  public function __get($key)
  {
    $attributes = $this->getAttribute('settings');
    if (array_key_exists($key, $attributes)) {
      return $attributes[$key];
    }
    return null;
  }

  public function __set($key, $value)
  {
    $attributes = $this->getAttributes();
    if (array_key_exists('settings', $attributes)) {
      $attributes['settings'] = array();
    }
    $attributes['settings'][$key] = $value;
    $this->setAttributes($attributes);
  }
}
