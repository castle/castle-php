<?php

namespace Castle\Test;

class RequestTransport
{
    public $rBody;
    public $rHeaders;
    public $rStatus;
    public $rError;
    public $rMessage;

    private static $params = array();

    private static $lastRequest = array();

    public function send($method, $url, $body = null, $headers = array())
    {
        if(empty(self::$params))
        {
            self::setResponse(200, '{}');
        }
        $headers_array = array();
        foreach($headers as $header)
        {
            preg_match('#(.*?)\:\s(.*)#', $header, $matches);
            if(empty($matches[1]))
            {
                $headers_array[$matches[1]] = $matches[2];
            }
        }
        $body = empty($body) ? null : json_decode($body, true);
        self::$lastRequest[] = array(
            'method' => $method,
            'headers' => $headers_array,
            'params' => $body,
            'url' => $url
        );
        $params = array_pop(self::$params);
        $this->rBody = $params['body'];
        $this->rStatus = $params['code'];
        $this->rHeaders = $params['headers'];
    }

    public static function getLastRequest()
    {
        return array_pop(self::$lastRequest);
    }

    public static function reset()
    {
        self::$params = array();
        self::$lastRequest = array();
    }

    public static function setResponse($code = 200, $body = '', $headers = array())
    {
        if(is_array($body))
        {
            $body = json_encode($body, true);
        }
        self::$params[] = array(
            'body' => $body,
            'code' => $code,
            'headers' => $headers
        );
    }
}