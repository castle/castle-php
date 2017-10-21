<?php

class Castle_RequestContext
{
  public $clientId;

  public $headers;

  public $ip;

  public $body;

  public function toRequestHeaders() {
    $client = Castle_Request::clientUserAgent();
    return array(
      'X-Castle-Client-Id: ' . $this->clientId,
      'X-Castle-Headers: ' . $this->headers,
      'X-Castle-Ip: ' . $this->ip,
      'X-Castle-Client-User-Agent: ' . $client,
      'Content-Type: application/json',
      'Content-Length: ' . strlen($this->body)
    );
  }

  public function validate() {

  }

  public static function build(array $properties) {
    $instance = new self();
    foreach ($properties as $key => $value){
      if ( property_exists ( $instance , $key ) ){
        $instance->$key = $value;
      }
    }
    return $instance;
  }
}
