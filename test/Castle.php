<?php

abstract class Castle_TestCase extends \PHPUnit\Framework\TestCase
{
  public function setUp(): void
  {
    Castle::setApiKey('secret');
  }

  public function assertRequest($method, $url, $headers=null)
  {
    $request = Castle_RequestTransport::getLastRequest();
    $this->assertEquals($method, $request['method']);
    $this->assertEquals(Castle_Request::apiUrl($url), $request['url']);
    if (is_array($headers)) {
      foreach ($headers as $key => $value) {
        $this->assertArrayHasKey($key, $request['headers']);
        $this->assertEquals($request['headers'][$key], $value);
      }
    }
    return $request;
  }
}

require_once(dirname(__FILE__) . '/../lib/Castle/Castle.php');
require_once(dirname(__FILE__) . '/../lib/Castle/Errors.php');
require_once(dirname(__FILE__) . '/CookieStore.php');
require_once(dirname(__FILE__) . '/../lib/RestModel/Resource.php');
require_once(dirname(__FILE__) . '/../lib/RestModel/Model.php');
require_once(dirname(__FILE__) . '/../lib/Castle/Authenticate.php');
require_once(dirname(__FILE__) . '/../lib/Castle/Context.php');
require_once(dirname(__FILE__) . '/TestTransport.php');
require_once(dirname(__FILE__) . '/../lib/Castle/RequestContext.php');
require_once(dirname(__FILE__) . '/../lib/Castle/Request.php');
require_once(dirname(__FILE__) . '/../lib/Castle/Webhook.php');
require_once(dirname(__FILE__) . '/../lib/aliases.php');
