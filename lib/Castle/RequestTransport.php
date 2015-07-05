<?php

namespace Castle;

use Castle\Errors\RequestError;

class RequestTransport
{
    public $rBody;
    public $rHeaders;
    public $rStatus;
    public $rError;
    public $rMessage;

    public static function caCerts()
    {
        return dirname(dirname(dirname(__FILE__))) . '/certs/ca-certs.crt';
    }

    private function setResponse($curl)
    {
        $response = curl_exec($curl);

        $this->rError = null;
        $this->rMessage = null;
        $this->rBody = null;
        $this->rStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->rHeaders = array();

        if ($response == false) {
            $this->rError   = curl_errno($curl);
            $this->rMessage = curl_error($curl);
        }
        else {
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $this->rBody = substr($response, $header_size);
            $headers_string = substr($response, 0, $header_size);
            $headers_array = explode("\r\n", str_replace("\r\n\r\n", '', $headers_string));
            # Convert headers into an associative array
            foreach ($headers_array as $header) {
                preg_match('#(.*?)\:\s(.*)#', $header, $matches);
                if (!empty($matches[1])) {
                    $this->rHeaders[$matches[1]] = $matches[2];
                }
            }
        }
    }

    public function send($method, $url, $body=null, $headers=array()) {
        $curl = curl_init();
        $method = strtolower($method);
        switch($method) {
            case 'post':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                break;
            case 'get':
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                break;
            case 'put':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
            case 'delete':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                throw new RequestError();
        }
        $curlOptions = array();
        if (!empty($body)) {
            $curlOptions[CURLOPT_POSTFIELDS] = $body;
        }

        $curlOptions[CURLOPT_CAINFO] = self::caCerts();
        $curlOptions[CURLOPT_URL] = $url;
        $curlOptions[CURLOPT_USERPWD] = ":" . Castle::getApiKey();
        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_USERAGENT] = "Castle/v1 PHPBindings/".Castle::VERSION;
        $curlOptions[CURLOPT_CONNECTTIMEOUT] = 3;
        $curlOptions[CURLOPT_TIMEOUT] = 10;
        $curlOptions[CURLOPT_HTTPHEADER] = $headers;
        $curlOptions[CURLOPT_HEADER] = true;

        curl_setopt_array($curl, $curlOptions);

        $this->setResponse($curl);

        curl_close($curl);
    }
}
