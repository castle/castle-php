<?php
interface Userbin_iTrustedTokenStore
{
  public function destroy();
  public function read();
  public function write($data);
}

class Userbin_TrustedTokenStore implements Userbin_iTrustedTokenStore
{
  protected $key     = 'userbin.trusted_device_token';

  public function destroy()
  {
    if (isset($_COOKIE)) {
      unset($_COOKIE[$this->key]);
    }
  }

  public function read()
  {
    if (isset($_COOKIE) && array_key_exists($this->key, $_COOKIE)) {
      return $_COOKIE[$this->key];
    }
    return null;
  }

  public function write($data)
  {
    if (isset($_COOKIE)) {
      $_COOKIE[$this->key] = $data;
    }
  }
}
