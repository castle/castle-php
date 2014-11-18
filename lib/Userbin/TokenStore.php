<?php
interface Userbin_iSessionStore
{
  public function getSession();
  public function setSession($value);
  public function getTrustedDevice();
  public function setTrustedDevice($value);
}

/**
 * By default the session token is persisted in the PHP $_SESSION, which may
 * in turn point to any source. This option give you an option to
 * use any store, such as Redis or Memcached to store your Userbin tokens.
 */
class Userbin_TokenStore implements Userbin_iSessionStore
{
  protected $sessionKey       = '_ubt';
  protected $store            = null;
  protected $trustedDeviceKey = '_ubs';

  public function __construct($store = 'Userbin_CookieStore')
  {
    $this->store = new $store;
  }

  private function writeKey($key, $value = null)
  {
    if ($value == null) {
      $this->store->destroy($key);
    }
    else {
      $this->store->write($key, $value);
    }
  }

  public function getSession()
  {
    return $this->store->read($this->sessionKey);
  }

  public function setSession($value = null)
  {
    $this->writeKey($this->sessionKey, $value);
  }

  public function getTrustedDevice()
  {
    return $this->store->read($this->trustedDeviceKey);
  }

  public function setTrustedDevice($value = null)
  {
    $this->writeKey($this->trustedDeviceKey, $value);
  }

}
