<?php

class Userbin_RequestTransport
{
  public $rBody;
  public $rStatus;
  public $rError;
  public $rMessage;

  public function send($method, $url, $params=null, $headers=array()) {
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
        throw new Userbin_RequestError();
    }
    $curlOptions = array();
    if (!empty($vars)) {
      $data_string = json_encode($vars);
      $curlOptions[CURLOPT_POSTFIELDS] = $data_string;
      $headers[]= 'Content-Length: ' . strlen($data_string);
    }

    $curlOptions[CURLOPT_URL] = $url;
    $curlOptions[CURLOPT_USERPWD] = ":" . Userbin::getApiKey();
    $curlOptions[CURLOPT_HEADER] = true;
    $curlOptions[CURLOPT_RETURNTRANSFER] = true;
    $curlOptions[CURLOPT_USERAGENT] = "Userbin/v1 PHPBindings/".Userbin::VERSION;
    $curlOptions[CURLOPT_TIMEOUT] = 10;
    $curlOptions[CURLOPT_HTTPHEADER] = $headers;

    curl_setopt_array($curl, $curlOptions);
    $this->rBody = curl_exec($curl);

    $this->rStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($this->rBody == false) {
      $this->rError   = curl_errno($curl);
      $this->rMessage = curl_error($curl);
    }
    else {
      $this->rError = null;
      $this->rMessage = null;
    }
    curl_close($curl);
  }
}

?>