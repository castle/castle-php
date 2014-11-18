<?php
interface Userbin_iCookieStore
{
  public function destroy($key);
  public function read($key);
  public function write($key, $data);
}

class Userbin_CookieStore implements Userbin_iCookieStore
{
  public function destroy($key)
  {
    if (isset($_COOKIE)) {
      unset($_COOKIE[$key]);
    }
  }

  public function read($key)
  {
    if (isset($_COOKIE) && array_key_exists($key, $_COOKIE)) {
      return $_COOKIE[$key];
    }
    return null;
  }

  public function write($key, $data)
  {
    if (isset($_COOKIE)) {
      $_COOKIE[$key] = $data;
    }
  }
}
