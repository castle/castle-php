<?php

// PHPUnit 8.5 (used on PHP 7.2) relies on php-timer, which throws
// "Cannot determine time at which the request started" when REQUEST_TIME_FLOAT
// is absent from the CLI environment. Seed it so the timer can compute.
if (!isset($_SERVER['REQUEST_TIME_FLOAT'])) {
  $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
}

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
require_once(dirname(__FILE__) . '/../lib/Castle/Failover.php');
require_once(dirname(__FILE__) . '/CookieStore.php');
require_once(dirname(__FILE__) . '/../lib/RestModel/Resource.php');
require_once(dirname(__FILE__) . '/../lib/RestModel/Model.php');
require_once(dirname(__FILE__) . '/../lib/Castle/Context.php');
require_once(dirname(__FILE__) . '/TestTransport.php');
require_once(dirname(__FILE__) . '/../lib/Castle/RequestContext.php');
require_once(dirname(__FILE__) . '/../lib/Castle/Request.php');
require_once(dirname(__FILE__) . '/../lib/Castle/Webhook.php');
require_once(dirname(__FILE__) . '/../lib/aliases.php');
