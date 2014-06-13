<?php

class Userbin_SessionAdapter
{
  protected $key = 'userbin';

  public function destroy()
  {
    if (isset($_SESSION)) {
      unset($_SESSION[$this->key]);
    }
  }

  public function read()
  {
    if (isset($_SESSION) && array_key_exists($this->key, $_SESSION)) {
      return $_SESSION[$this->key];
    }
    return null;
  }

  public function write($value)
  {
    if (isset($_SESSION)) {
      $_SESSION[$this->key] = $value;
    }
  }
}
