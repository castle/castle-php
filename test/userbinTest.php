<?php

require_once 'userbin.php';

class UserbinTest extends \PHPUnit_Framework_TestCase
{
  public function testJavascriptContainsAppId()
  {
    Userbin::set_app_id('123456789');
    $this->assertContains('123456789', Userbin::javascript_include_tag());
  }
}

?>