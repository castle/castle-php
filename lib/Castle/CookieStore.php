<?php
interface Castle_iCookieStore
{
  public function destroy($key);
  public function hasKey($key);
  public function read($key);
  public function write($key, $data);
}

/**
 * By default the session token is persisted in a cookie
 */
class Castle_CookieStore implements Castle_iCookieStore
{
  public function destroy($key)
  {
    if (isset($_COOKIE)) {
      setcookie($key, $data, time() - 2592000, '/');
      unset($_COOKIE[$key]);
    }
  }

  public function hasKey($key)
  {
    return isset($_COOKIE) && array_key_exists($key, $_COOKIE);
  }

  public function read($key)
  {
    return self::hasKey($key) ? $_COOKIE[$key] : null;
  }

  public function write($key, $data)
  {
    setcookie($key, $data, time() + 2592000, '/');
    if (isset($_COOKIE)) {
      $_COOKIE[$key] = $data;
    }
  }
}
