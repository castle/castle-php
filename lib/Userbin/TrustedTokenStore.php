<?php
interface Userbin_iTrustedTokenStore
{
  public function destroy();
  public function read();
  public function write($data);
}

/**
 * By default the session token is persisted in a cookie
 */
class Userbin_TrustedTokenStore implements Userbin_iTrustedTokenStore
{
  protected $key     = '_ubt';

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
    setcookie($this->key, $data, time() + 2592000, '/');
    if (isset($_COOKIE)) {
      $_COOKIE[$this->key] = $data;
    }
  }
}
