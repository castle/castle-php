<?php

class Userbin_SessionSerializer
{
  public function destroy()
  {
    unset($_SESSION['userbin']);
  }

  public function read()
  {
    if (array_key_exists('userbin', $_SESSION)) {
      return $_SESSION['userbin'];
    }
    return null;
  }

  public function write($value)
  {
    $_SESSION['userbin'] = $value;
  }
}