<?php

namespace Castle;

interface iCookieStore
{
  public function destroy($key);
  public function read($key);
  public function write($key, $data);
}

/**
 * By default the session token is persisted in a cookie
 */
class CookieStore implements iCookieStore
{
  public function destroy($key)
  {
    if (isset($_COOKIE)) {
      setcookie($key, $data, time() - 2592000, '/');
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
    setcookie($key, $data, time() + 2592000, '/');
    if (isset($_COOKIE)) {
      $_COOKIE[$key] = $data;
    }
  }
}
