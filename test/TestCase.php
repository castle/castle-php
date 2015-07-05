<?php

namespace Castle\Test;

use Castle\Castle;
use Castle\Request;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        Castle::setApiKey('secret');
    }

    public function assertRequest($method, $url, $headers = null)
    {
        $request = RequestTransport::getLastRequest();
        $this->assertEquals($method, $request['method']);
        $this->assertEquals(Request::apiUrl($url), $request['url']);
        if(is_array($headers))
        {
            foreach($headers as $key => $value)
            {
                $this->assertArrayHasKey($key, $request['headers']);
                $this->assertEquals($request['headers'][$key], $value);
            }
        }
        return $request;
    }
}

require(dirname(dirname(__FILE__)) . '/vendor/autoload.php');