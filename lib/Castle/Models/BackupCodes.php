<?php

class Castle_BackupCodes extends RestModel
{
  protected $isSingular = true;

  public function generate()
  {
    $this->post();
  }
}
