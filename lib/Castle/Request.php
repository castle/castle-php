<?php

class Castle_Request
{
  public static function apiUrl($url='')
  {
    $apiEndpoint = getenv('CASTLE_API_ENDPOINT');
    if ( !$apiEndpoint ) {
      $apiBase    = Castle::$apiBase;
      $apiVersion = Castle::getApiVersion();
      $apiEndpoint = $apiBase.'/'.$apiVersion;
    }
    return $apiEndpoint.$url;
  }

  public function handleApiError($response, $status)
  {
    $response = is_array($response) ? $response : array();
    $type = isset($response['type']) ? $response['type'] : null;
    $msg  = isset($response['message']) ? $response['message'] : null;
    switch ($status) {
      case 400:
        throw new Castle_BadRequest($msg, $type, $status);
      case 401:
        throw new Castle_UnauthorizedError($msg, $type, $status);
      case 403:
        throw new Castle_ForbiddenError($msg, $type, $status);
      case 404:
        throw new Castle_NotFoundError($msg, $type, $status);
      case 422:
        // Handle subtype errors
        switch($type) {
          case 'invalid_request_token':
            throw new Castle_InvalidRequestTokenError($msg, $type, $status);
          default:
            throw new Castle_InvalidParametersError($msg, $type, $status);
        }
      default:
        throw new Castle_ApiError($msg, $type, $status);
    }
  }

  public function handleRequestError($request)
  {
    throw new Castle_RequestError("$request->rError: $request->rMessage");
  }

  public function handleResponse($request)
  {
    if ($request->rError) {
      $this->handleRequestError($request);
    }

    $response = json_decode($request->rBody, true);
    if (!empty($request->rBody) && $response === null) {
      throw new Castle_ApiError('Invalid response from API', 'api_error', $request->rStatus);
    }

    if ($request->rStatus < 200 || $request->rStatus >= 300) {
      $this->handleApiError($response, $request->rStatus);
    }

    return array($response, $request);
  }

  public function preFlightCheck()
  {
    $key = Castle::getApiKey();
    if (empty($key)) {
      throw new Castle_ConfigurationError();
    }
  }

  public function send($method, $url, $payload = array()) {
    if (!is_array($payload)) {
      $payload = array();
    }

    if ( self::shouldHaveContext($url) && !array_key_exists('context', $payload)) {
      $payload['context'] = Castle_RequestContext::extract();
    }

    if ( self::shouldHaveSentAt($url) && !array_key_exists('sent_at', $payload)) {
      $payload['sent_at'] = self::generateTimestamp();
    }

    return $this->sendWithContext($url, $payload, $method);
  }

  private function shouldHaveContext($url) {
    $WITH_CONTEXT = ['/track', '/authenticate', '/impersonate', '/risk', '/filter', '/log'];

    return in_array($url, $WITH_CONTEXT);
  }

  private function shouldHaveSentAt($url) {
    $WITH_SENT_AT = ['/risk', '/filter', '/log'];

    return in_array($url, $WITH_SENT_AT);
  }

  // ISO8601 timestamp (millisecond precision, UTC) marking when the request was sent.
  public static function generateTimestamp() {
    $date = new DateTime('now', new DateTimeZone('UTC'));
    return $date->format('Y-m-d\TH:i:s.v\Z');
  }

  public function sendWithContext($url, $payload, $method = 'post')
  {
    $this->preFlightCheck();


    $request = new Castle_RequestTransport();
    $request->send($method, self::apiUrl($url), $payload);

    return $this->handleResponse($request);
  }
}
