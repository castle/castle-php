<?php

class Userbin_Request
{
  public static function apiUrl($url='')
  {
    $apiBase    = Userbin::$apiBase;
    $apiVersion = Userbin::getApiVersion();
    return $apiBase."/".$apiVersion.$url;
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

  public function handleApiError($response, $status)
  {
    $type = $response['type'];
    $msg  = $response['message'];
    switch ($status) {
      case 401:
        throw new Userbin_UnauthorizedError($type, $msg, $status);
      case 403:
        throw new Userbin_ForbiddenError($type, $msg, $status);
      case 419:
        throw new Userbin_UserUnauthorizedError($type, $msg, $status);
      case 422:
        throw new Userbin_InvalidParametersError($type, $msg, $status);
      default:
        throw new Userbin_ApiError($type, $msg, $status);
    }
  }

  public function handleRequestError($request)
  {
    throw new Userbin_RequestError("$request->rError: $request->rMessage");
  }

  public function send($method, $url, $params=null)
  {
    $client = Userbin_Request::clientUserAgent();
    $headers = array(
      'X-Userbin-User-Agent: ' . $_SERVER['HTTP_USER_AGENT'],
      'X-Userbin-Ip: ' . $_SERVER['REMOTE_ADDR'],
      'X-Userbin-Client-User-Agent: ' . $client,
      'Content-Type: application/json'
    );

    // Check if there is a current session and pass it along
    $session = Userbin::getSessionStore()->read();
    if (isset($session)) {
      $headers[]= 'X-Userbin-Session-Token: '.$session;
    }

    $request = new Userbin_RequestTransport();
    $request->send($method, self::apiUrl($url), $params, $headers);

    if ($request->rError) {
      $this->handleRequestError($request);
    }

    try {
      $response = json_decode($request->rBody, true);
    } catch (Exception $e) {
      throw new Userbin_ApiError('api_error', 'Invalid response from API', $request->rStatus);
    }

    if ($request->rStatus < 200 || $request->rStatus >= 300) {
      $this->handleApiError($response, $request->rStatus);
    }

    // Update the local session if it was updated by Userbin
    if (array_key_exists('X-Userbin-Session-Token', $request->rHeaders)) {
      Userbin::getSessionStore()->write($request->rHeaders['X-Userbin-Session-Token']);
    }

    return array($response, $request);
  }
}
