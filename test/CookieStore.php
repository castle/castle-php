<?php
interface Castle_iCookieStore
{
  public function destroy($key);
  public function read($key);
  public function hasKey($key);
  public function write($key, $data);
}

class Castle_CookieStore implements Castle_iCookieStore
{
  public function destroy($key)
  {
    if (isset($_COOKIE)) {
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
    if (isset($_COOKIE)) {
      $_COOKIE[$key] = $data;
    }
  }
}
