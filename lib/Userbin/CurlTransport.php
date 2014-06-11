<?php

class Userbin_RequestTransport
{
  public $rBody;
  public $rHeaders;
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
    if (!empty($params)) {
      $data_string = json_encode($params);
      $curlOptions[CURLOPT_POSTFIELDS] = $data_string;
      $headers[]= 'Content-Length: ' . strlen($data_string);
    }

    $curlOptions[CURLOPT_URL] = $url;
    $curlOptions[CURLOPT_USERPWD] = ":" . Userbin::getApiKey();
    $curlOptions[CURLOPT_RETURNTRANSFER] = true;
    $curlOptions[CURLOPT_USERAGENT] = "Userbin/v1 PHPBindings/".Userbin::VERSION;
    $curlOptions[CURLOPT_TIMEOUT] = 10;
    $curlOptions[CURLOPT_HTTPHEADER] = $headers;
    $curlOptions[CURLOPT_HEADER] = true;

    curl_setopt_array($curl, $curlOptions);
    $response = curl_exec($curl);

    $this->rStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $this->rHeaders = array();

    if ($response == false) {
      $this->rError   = curl_errno($curl);
      $this->rMessage = curl_error($curl);
      $this->rBody = false;
    }
    else {
      $this->rError = null;
      $this->rMessage = null;
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

    curl_close($curl);
  }
}

?>