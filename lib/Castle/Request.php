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
    $type = $response['type'];
    $msg  = $response['message'];
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
        throw new Castle_InvalidParametersError($msg, $type, $status);
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

  public function send($method, $url, $payload = 's') {
    if ( self::shouldHaveContext($url) && !array_key_exists('context', $payload)) {
      $payload['context'] = Castle_RequestContext::extract();
    }

    return $this->sendWithContext($url, $payload, $method);
  }

  private function shouldHaveContext($url) {
    $WITH_CONTEXT = ['/track', '/authenticate', '/impersonate'];

    return in_array($url, $WITH_CONTEXT);
  }

  public function sendWithContext($url, $payload, $method = 'post')
  {
    $this->preFlightCheck();


    $request = new Castle_RequestTransport();
    $request->send($method, self::apiUrl($url), $payload);

    return $this->handleResponse($request);
  }
}
