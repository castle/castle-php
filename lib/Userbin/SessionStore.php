<?php
interface Userbin_iSessionStore
{
  public function destroy();
  public function read();
  public function getUserId();
  public function setUserId($userId);
  public function write($data);
}

/**
 * By default the session token is persisted in the PHP $_SESSION, which may
 * in turn point to any source. This option give you an option to
 * use any store, such as Redis or Memcached to store your Userbin tokens.
 */
class Userbin_SessionStore implements Userbin_iSessionStore
{
  protected $key     = 'userbin';
  protected $userKey = 'userbin.user_id';

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

  public function getUserId()
  {
    if (isset($_SESSION) && array_key_exists($this->userKey, $_SESSION)) {
      return $_SESSION[$this->userKey];
    }
    return null;
  }

  public function setUserId($userId)
  {
    if (isset($_SESSION)) {
      $_SESSION[$this->userKey] = $userId;
    }
  }

  public function write($data)
  {
    if (isset($_SESSION)) {
      $_SESSION[$this->key] = $data;
    }
  }
}
