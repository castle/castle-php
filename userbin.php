<?php
/**
 * Helper library for Userbin service
 */

if (!function_exists('curl_init')) {
  throw new Exception('Userbin needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Userbin needs the JSON PHP extension.');
}

class Userbin {
  public static $appId = "800000000000000";
  public static $apiSecret;
  public static $scriptUrl = '//js.userbin.com';
  public static $apiUrl = 'https://api.userbin.com';
  public static $apiVer = 'v0';
  public static $locale = false;
  public static $javascript_settings = array(
    'loginRedirectUrl' => false,
    'logoutRedirectUrl' => false,
    'reloadOnSuccess' => 'true'
  );
  const VERSION = '0.1.0';

  /*
   * Public methods
   */

  /**
   * Sync session with Userbin. Should always be run early.
   * @return bool True if user is logged in. False otherwise
   */
  public static function authenticate() {
    self::verify_settings();
    $session = self::get_session();
    /* Renew session if expired */
    if($session && self::has_expired()) {
      self::clear_session();
      $request = new UserbinRequest();
      $response = $request->post('sessions/'.$session['id'].'/refresh.jwt');

      if($response) {
        self::set_session($response->body);
      }
    }
    return self::authenticated();
  }

  /**
   * Check if there is logged in user
   * @return bool True if user is logged in. False otherwise
   */
  public static function authenticated() {
    self::verify_settings();
    return !!self::user();
  }

  /**
   * Configure the javascript
   * @param  $options An associative array containing configuration options
   * @return none
   */

  public static function configure($options) {
    if (isset($options['protected_path']))
      self::$javascript_settings['loginRedirectUrl'] = '"'.$options['protected_path'].'"';

    if (isset($options['root_path']))
      self::$javascript_settings['logoutRedirectUrl'] = '"'.$options['root_path'].'"';

    if (isset($options['locale']))
      self::$locale = $options['locale'];
  }

  /**
   * Generates a script tag that includes the Userbin javascript
   * with the configured App ID.
   * @param  array  $options Optional array with settings
   * @return string          HTML script tag
   */
  public static function javascript_include_tag($options = false) {
    self::verify_settings();
    if(isset($options))
      self::configure($options);
    $aId = self::$appId;
    $url = self::$scriptUrl;
    if(self::$locale)
      $lang = "&lang=".self::$locale;
    $html = "\n<script type=\"text/javascript\" src=\"$url?$aId$lang\"></script>\n";
    $html.= "<script type=\"text/javascript\">\n";
    $html.= "  Userbin.config({";
    $opts = array();
    foreach(self::$javascript_settings as $key => $val) {
      if(!!$val)
        $opts[]= "\"$key\": $val";
    }
    $opts = join(", ", $opts);
    $html.= "$opts});\n</script>\n";
    return $html;
  }

  public static function protect() {
    if (!self::authenticate()) {
      header("HTTP/1.0 403 Forbidden");
      die("<!DOCTYPE html>\n".
           "<html>\n".
           "<head>\n".
           "  <meta charset='utf-8'>\n".
           "  <meta http-equiv='X-UA-Compatible' content='IE=edge'>\n".
           "  <title>Log in</title>\n".
           "  <meta name='viewport' content='width=device-width, initial-scale=1'>\n".
           "</head>\n".
           "<body>\n".
           "<a class='ub-login-form'></a>\n".
           "</body>\n".
           self::javascript_include_tag().
           "</html>\n");
    }
  }

  /**
   * Set the Userbin App ID. Sign up at https://userbin.com to obtain one.
   * @param int $appId 15 digit App ID
   */
  public static function set_app_id($appId) {
    self::$appId = $appId;
  }

  /**
   * Set the Userbin API secret. Sign up at https://userbin.com to obtain one.
   * @param string $apiSecret 32 byte long API secret
   */
  public static function set_api_secret($apiSecret) {
    self::$apiSecret = $apiSecret;
  }

  /**
   * Gets the currently logged in user as an associative array.
   * @return array Associative array if logged in. False otherwise
   */
  public static function user() {
    self::verify_settings();
    $session = self::get_session();
    if($session) {
      return $session['user'];
    }
    return false;
  }

  /**
   * Verifies the signature of a string
   * @param  string $signature The signature to test against
   * @param  string $data      The data that has been signed
   * @return bool              True if signature matches, ie. the data comes
   *                           from a trusted source. False otherwise
   */
  public static function valid_signature($signature, $data) {
    return $signature == self::signature($data);
  }

  /*
   * Private methods
   */
  private static function signature($data) {
    return hash_hmac('sha256', $data, self::$apiSecret, false);
  }

  private static function has_expired() {
    $session = self::get_session();
    if(!$session) return false;
    return time() > ($session['expires_at'] / 1000);
  }

  private static function clear_session() {
    setcookie('_ubt', '', time()-3600, '/');
    unset($_COOKIE['_ubt']);
    return true;
  }

  private static function get_session() {
    if (!$_COOKIE['_ubt']) {
      return false;
    }
    $jwt = new UserbinJWT($_COOKIE['_ubt']);
    if (!$jwt->is_valid()) return false;
    return $jwt->payload();
  }

  private static function set_session($data) {
    $jwt = new UserbinJWT($data);
    if($jwt->is_valid()) {
      setcookie('_ubt', $data, 0, '/');
      $_COOKIE['_ubt'] = $data;
      return true;
    }
    return false;
  }

  private static function verify_settings() {
    if (getenv(USERBIN_APP_ID)) {
      self::set_app_id(getenv(USERBIN_APP_ID));
    }
    if (getenv(USERBIN_API_SECRET)) {
      self::set_api_secret(getenv(USERBIN_API_SECRET));
    }
    if(!(self::$appId || self::$apiSecret)) {
      throw new Exception('Please set Userbin App ID and API secret');
    }
  }
}

/**
 * Wrapper for cURL request
 */
class UserbinRequest {
  function request($method, $url, $vars = array()) {
    $this->error = '';
    $this->_request = curl_init();

    $url = join('/', array(Userbin::$apiUrl, $url));

    if (is_array($vars)) $vars = http_build_query($vars, '', '&');

    switch(strtoupper($method)) {
      case 'POST':
        curl_setopt($this->_request, CURLOPT_POST, true);
        break;
      case 'GET':
        curl_setopt($this->_request, CURLOPT_HTTPGET, true);
        break;
      default:
        return false;
    }
    curl_setopt($this->_request, CURLOPT_URL, $url);
    if (!empty($vars)) curl_setopt($this->_request, CURLOPT_POSTFIELDS, $vars);
    curl_setopt($this->_request, CURLOPT_USERPWD, Userbin::$appId . ":" . Userbin::$apiSecret);
    curl_setopt($this->_request, CURLOPT_HEADER, true);
    curl_setopt($this->_request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->_request, CURLOPT_USERAGENT, 'Curl/PHP '.PHP_VERSION);
    curl_setopt($this->_request, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($this->_request);
    if (!$response) {
      $this->error = curl_errno($this->_request).' - '.curl_error($this->_request);
    } else {
      $response = new UserbinResponse($response);
    }

    curl_close($this->_request);

    return $response;
  }

  function post($url, $vars = array()) {
    return $this->request('POST', $url, $vars);
  }
}

/**
 * Wrapper for HTTP response
 */
class UserbinResponse {
  function __construct($response) {
    $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';

    preg_match_all($pattern, $response, $matches);
    $headers_string = array_pop($matches[0]);
    $headers = explode("\r\n", str_replace("\r\n\r\n", '', $headers_string));

    $this->body = str_replace($headers_string, '', $response);

    $version_and_status = array_shift($headers);
    preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches);
    $this->headers['Http-Version'] = $matches[1];
    $this->headers['Status-Code'] = $matches[2];
    $this->headers['Status'] = $matches[2].' '.$matches[3];

    foreach ($headers as $header) {
        preg_match('#(.*?)\:\s(.*)#', $header, $matches);
        $this->headers[$matches[1]] = $matches[2];
    }
    /* Extract signature */
    $this->signature = $this->headers['X-Userbin-Signature'];
  }

  function as_json() {
    return json_decode($this->body, true);
  }

  function is_valid() {
    return Userbin::valid_signature($this->signature, $this->body);
  }
}

class UserbinJWT {
  function __construct($token) {
    list($this->_header, $this->_token, $this->_signature) = explode(".", $token);
  }

  public function is_valid() {
    $hmac = hash_hmac('sha256', "$this->_header.$this->_token", Userbin::$apiSecret, true);
    return self::base64_encode($hmac) == $this->_signature;
  }

  public function header() {
    return json_decode(self::base64_decode($this->_header), true);
  }

  public function payload() {
    return json_decode(self::base64_decode($this->_token), true);
  }

  public static function base64_encode($data) {
    return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
  }

  public static function base64_decode($data) {
    $rem = strlen($data) % 4;
    if ($rem) {
      $pad = 4 - $rem;
      $data .= str_repeat('=', $pad);
    }
    return base64_decode(strtr($data, '-_', '+/'));
  }

}

?>
