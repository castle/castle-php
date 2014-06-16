<?php
interface Userbin_iSessionStore
{
  public function destroy();
  public function read();
  public function write($data);
}


class Userbin_SessionStore implements Userbin_iSessionStore
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

  public function write($data)
  {
    if (isset($_SESSION)) {
      $_SESSION[$this->key] = $data;
    }
  }
}
