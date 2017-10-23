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

  public function send($method, $url, $params=null)
  {
    $context = Castle_RequestContext::extract($params);

    return $this->sendWithContext($url, $context, $method);
  }

  public function sendWithContext($url, $context, $method = 'post')
  {
    $this->preFlightCheck();


    $request = new Castle_RequestTransport();
    $request->send($method, self::apiUrl($url), $context);

    return $this->handleResponse($request);
  }
}
