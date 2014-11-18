<?php

class Userbin_Request
{
  public static function apiUrl($url='')
  {
    $apiEndpoint = getenv('USERBIN_API_ENDPOINT');
    if ( !$apiEndpoint ) {
      $apiBase    = Userbin::$apiBase;
      $apiVersion = Userbin::getApiVersion();
      $apiEndpoint = $apiBase.'/'.$apiVersion;
    }
    return $apiEndpoint.$url;
  }

  public static function clientUserAgent()
  {
    $langVersion = phpversion();
    $uname = php_uname();
    $userAgent = array(
      'bindings_version' => Userbin::VERSION,
      'lang' => 'php',
      'lang_version' => $langVersion,
      'platform' => PHP_OS,
      'publisher' => 'userbin',
      'uname' => $uname
    );
    return json_encode($userAgent);
  }

  public static function getIp()
  {
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
      $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
      return $parts[0];
    }
    if (array_key_exists('HTTP_X_REAL_IP', $_SERVER)) {
      return $_SERVER['HTTP_X_REAL_IP'];
    }
    if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
      return $_SERVER['REMOTE_ADDR'];
    }
    return null;
  }

  public function handleApiError($response, $status)
  {
    $type = $response['type'];
    $msg  = $response['message'];
    switch ($status) {
      case 400:
        throw new Userbin_BadRequest($msg, $type, $status);
      case 401:
        throw new Userbin_UnauthorizedError($msg, $type, $status);
      case 403:
        throw new Userbin_ForbiddenError($msg, $type, $status);
      case 404:
        throw new Userbin_NotFoundError($msg, $type, $status);
      case 419:
        /* Clear session since this error means that is is invalid or removed */
        Userbin::getSessionStore()->destroy();
        throw new Userbin_UserUnauthorizedError($msg, $type, $status);
      case 422:
        throw new Userbin_InvalidParametersError($msg, $type, $status);
      default:
        throw new Userbin_ApiError($msg, $type, $status);
    }
  }

  public function handleRequestError($request)
  {
    throw new Userbin_RequestError("$request->rError: $request->rMessage");
  }

  public function handleResponse($request)
  {
    if ($request->rError) {
      $this->handleRequestError($request);
    }

    $response = json_decode($request->rBody, true);
    if (!empty($request->rBody) && $response === null) {
      throw new Userbin_ApiError('Invalid response from API', 'api_error', $request->rStatus);
    }

    if ($request->rStatus < 200 || $request->rStatus >= 300) {
      $this->handleApiError($response, $request->rStatus);
    }

    // Update the local session if it was updated by Userbin
    if (array_key_exists('X-Userbin-Set-Session-Token', $request->rHeaders)) {
      Userbin::setSessionToken($request->rHeaders['X-Userbin-Set-Session-Token']);
    }

    return array($response, $request);
  }

  public function preFlightCheck()
  {
    $key = Userbin::getApiKey();
    if (empty($key)) {
      throw new Userbin_ConfigurationError();
    }
  }

  public function send($method, $url, $params=null)
  {
    $this->preFlightCheck();

    $client = Userbin_Request::clientUserAgent();
    $body = empty($params) ? null : json_encode($params);
    $headers = array(
      'X-Userbin-User-Agent: ' . $_SERVER['HTTP_USER_AGENT'],
      'X-Userbin-Ip: ' . self::getIp(),
      'X-Userbin-Client-User-Agent: ' . $client,
      'Content-Type: application/json',
      'Content-Length: ' . strlen($body)
    );

    // Check if there is a current session and pass it along
    $session = Userbin::getSessionStore()->read();
    if (isset($session)) {
      $headers[]= 'X-Userbin-Session-Token: '.$session;
    }

    $request = new Userbin_RequestTransport();
    $request->send($method, self::apiUrl($url), $body, $headers);

    return $this->handleResponse($request);
  }
}
