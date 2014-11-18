<?php

class UserbinEventTest extends Userbin_TestCase
{
  public static function setUpBeforeClass()
  {
    $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
    $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
  }

  public function tearDown()
  {
    Userbin_RequestTransport::reset();
  }

  public function exampleEvent()
  {
    return array(
      array(array(
        'id' => 1,
        'context' => array(
          "ip" => "127.0.0.1",
          "user_agent" => array(
            "raw" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) ...",
            "browser" => "Firefox",
            "version" => "31.0",
            "platform" => "Macintosh",
            "os" => "OS X 10.9.5",
            "mobile" => false
          ),
          "location" => array(
            "country" => "United States",
            "country_code" => "US",
            "region" => "California",
            "region_code" => "CA",
            "city" => "San Francisco",
            "longitude" => -55.654325,
            "latitude" => 13.043243
          )
        )
      ))
    );
  }

  /**
   * @dataProvider exampleEvent
   */
  public function testEventContext($eventData)
  {
    $event = new Userbin_Event($eventData);
    $context = $event->context();
    $this->assertInstanceOf('Userbin_Context', $context);
  }

  /**
   * @dataProvider exampleEvent
   */
  public function testEventContextLocation($eventData)
  {
    $event = new Userbin_Event($eventData);
    $context = $event->context();
    $this->assertEquals('United States', $context->location()->country);
  }

  /**
   * @dataProvider exampleEvent
   */
  public function testEventContextUserAgent($eventData)
  {
    $event = new Userbin_Event($eventData);
    $context = $event->context();
    $this->assertEquals('Firefox', $context->userAgent()->browser);
  }
}
