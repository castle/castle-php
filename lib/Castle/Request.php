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

  public static function clientUserAgent()
  {
    $langVersion = phpversion();
    $uname = php_uname();
    $userAgent = array(
      'bindings_version' => Castle::VERSION,
      'lang' => 'php',
      'lang_version' => $langVersion,
      'platform' => PHP_OS,
      'publisher' => 'castle',
      'uname' => $uname
    );
    return json_encode($userAgent);
  }

  public static function getHeaders()
  {
    $headers = array();
    foreach ($_SERVER as $key => $val) {
      // Find all HTTP_ headers, convert '_' to '-' and uppercase every word
      if (substr($key, 0, 5) == 'HTTP_') {
        $name = strtolower(substr($key, 5));
        if (strpos($name, '_') != -1) {
          $name = preg_replace('/ /', '-', ucwords(preg_replace('/_/', ' ', $name)));
        } else {
          $name = ucfirst($name);
        }
        // If using whitelist, only use headers *PRESENT* in it.
        // If using scrublist, only use headers *NOT* present in it.
        if (Castle::getUseWhitelist() ?
              in_array($name, Castle::$whitelistHeaders) :
              !in_array($name, Castle::$scrubHeaders)) {
          $headers[$name] = $val;
        }
      }
    }
    return $headers;
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

  public static function getUserAgent()
  {
    if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
      return $_SERVER['HTTP_USER_AGENT'];
    }
    return null;
  }

  public static function normalize($cid) {
    $cid = preg_replace("/[[:cntrl:][:space:]]/", '', $cid);

    // If we end up with an empty/invalid cid, we'll set it to the special
    // value '_' to indicate there was a value but it was not valid.
    // This is to prevent curl from removing empty headers
    return empty($cid) ? '_' : $cid;
  }

  public static function getClientId() {
    if (array_key_exists('HTTP_X_CASTLE_DEVICE_ID', $_SERVER)) {
      return self::normalize($_SERVER['HTTP_X_CASTLE_DEVICE_ID']);
    } else if (Castle::getCookieStore()->hasKey('__cid')) {
      return self::normalize(Castle::getCookieStore()->read('__cid'));
    } else {
      // If the client_id is neither send in the header nor cookie
      // we'll return the special value '?'. This doesn't have any effect on
      // functionality. This is to prevent curl from removing empty headers
      return '?';
    }
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
      case 419:
        /* Clear session since this error means that is is invalid or removed */
        Castle::getTokenStore()->setSession();
        throw new Castle_UserUnauthorizedError($msg, $type, $status);
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

    // Update the local session if it was updated by Castle
    if (array_key_exists('X-Castle-Set-Session-Token', $request->rHeaders)) {
      Castle::setSessionToken($request->rHeaders['X-Castle-Set-Session-Token']);
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
    $this->preFlightCheck();

    $client = self::clientUserAgent();
    $body = empty($params) ? null : json_encode($params);
    $requestHeaders = json_encode(self::getHeaders());

    $headers = array(
      'X-Castle-Client-Id: ' . self::getClientId(),
      'X-Castle-User-Agent: ' . self::getUserAgent(),
      'X-Castle-Headers: ' . $requestHeaders,
      'X-Castle-Ip: ' . self::getIp(),
      'X-Castle-Client-User-Agent: ' . $client,
      'Content-Type: application/json',
      'Content-Length: ' . strlen($body)
    );

    // Check if there is a current session and pass it along
    $session = Castle::getTokenStore()->getSession();
    if (isset($session)) {
      $headers[]= 'X-Castle-Session-Token: '.$session;
    }

    $request = new Castle_RequestTransport();
    $request->send($method, self::apiUrl($url), $body, $headers);

    return $this->handleResponse($request);
  }
}
