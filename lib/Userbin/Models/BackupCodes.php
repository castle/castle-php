<?php

class Userbin_BackupCodes extends RestModel
{
  protected $isSingular = true;

  public function generate()
  {
    $this->post();
  }
}
