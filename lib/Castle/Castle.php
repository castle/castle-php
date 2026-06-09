<?php

namespace Castle;

abstract class Castle
{
  const VERSION = '4.0.0';

  const HEADER_COOKIE = 'Cookie';
  const HEADER_USER_AGENT = 'User-Agent';

  public static $apiKey;

  public static $baseUrl = 'https://api.castle.io/v1';

  public static $scrubHeaders = array(self::HEADER_COOKIE);

  private static $useAllowlist = false;
  public static $allowlistedHeaders = array(self::HEADER_USER_AGENT);

  // Reference allowlist of headers that are safe to forward. Not applied by
  // default; opt in with setUseAllowlist(true) and assign $allowlistedHeaders.
  const DEFAULT_ALLOWLIST = array(
    'Accept', 'Accept-Charset', 'Accept-Datetime', 'Accept-Encoding',
    'Accept-Language', 'Cache-Control', 'Connection', 'Content-Length',
    'Content-Type', 'Dnt', 'Host', 'Origin', 'Pragma', 'Referer',
    'Sec-Fetch-Dest', 'Sec-Fetch-Mode', 'Sec-Fetch-Site', 'Sec-Fetch-User',
    'Te', 'Upgrade-Insecure-Requests', 'User-Agent', 'X-Requested-With'
  );

  // Per-request timeout in milliseconds, applied to both connect and overall
  // transfer.
  public static $requestTimeout = 1000;

  // Decision returned when a request fails over. One of the Castle\Failover
  // strategy constants: 'allow', 'deny', 'challenge' or 'throw'.
  public static $failoverStrategy = 'allow';

  private static $doNotTrack = false;

  public static function getApiKey()
  {
    return self::$apiKey;
  }

  public static function setApiKey($apiKey)
  {
    self::$apiKey = $apiKey;
  }

  public static function getBaseUrl()
  {
    return self::$baseUrl;
  }

  public static function setBaseUrl($baseUrl)
  {
    self::$baseUrl = $baseUrl;
  }

  public static function getUseAllowlist()
  {
    return self::$useAllowlist;
  }

  public static function setUseAllowlist($use)
  {
    // Force User-Agent to be present in allowlisted if it is not.
    if ($use && !in_array(self::HEADER_USER_AGENT, self::$allowlistedHeaders)) {
      self::$allowlistedHeaders[] = self::HEADER_USER_AGENT;
    }
    self::$useAllowlist = $use;
  }

  /**
   * Filter an action
   * @param  Array $attributes 'request_token', 'event' and 'context' are required, 'user' with 'id' and 'properties' are optional
   * @return RestModel
   */
  public static function filter(array $attributes)
  {
    return self::trackingRequest('/filter', $attributes);
  }

  /**
   * Log events
   * @param  Array $attributes 'request_token', 'event', 'status' and 'user' object with 'id' are required
   * @return RestModel
   */
  public static function log(array $attributes)
  {
    return self::trackingRequest('/log', $attributes);
  }

  /**
   * Risk
   * @param  Array $attributes 'request_token', 'event', 'context' and 'user' with 'id' are required, 'status' and 'properties' are optional
   * @return RestModel
   */
  public static function risk(array $attributes)
  {
    return self::trackingRequest('/risk', $attributes);
  }

  /**
   * Stop sending tracking calls. While disabled, risk/filter/log return an
   * 'allow' response without contacting the API.
   */
  public static function disableTracking()
  {
    self::$doNotTrack = true;
  }

  /**
   * Resume sending tracking calls.
   */
  public static function enableTracking()
  {
    self::$doNotTrack = false;
  }

  public static function tracked()
  {
    return !self::$doNotTrack;
  }

  public static function getFailoverStrategy()
  {
    return self::$failoverStrategy;
  }

  public static function setFailoverStrategy($strategy)
  {
    if (!in_array($strategy, Failover::strategies(), true)) {
      throw new ConfigurationError('unrecognized failover strategy');
    }
    self::$failoverStrategy = $strategy;
  }

  public static function getRequestTimeout()
  {
    return self::$requestTimeout;
  }

  public static function setRequestTimeout($milliseconds)
  {
    self::$requestTimeout = $milliseconds;
  }

  private static function trackingRequest($path, array $attributes)
  {
    if (!self::tracked()) {
      return self::doNotTrackResponse(self::failoverUserId($attributes));
    }
    try {
      $request = new Request();
      list($response, $request) = $request->send('post', $path, $attributes);
      if ($request->rStatus == 204) {
        $response = array();
      }
      $response = is_array($response) ? $response : array();
      $response['failover'] = false;
      $response['failover_reason'] = null;
      return new RestModel($response);
    } catch (RequestError $e) {
      return self::failoverResponseOrRaise(self::failoverUserId($attributes), $e);
    } catch (InternalServerError $e) {
      return self::failoverResponseOrRaise(self::failoverUserId($attributes), $e);
    }
  }

  private static function failoverResponseOrRaise($userId, $exception)
  {
    if (self::$failoverStrategy === Failover::THROW) {
      throw $exception;
    }
    return new RestModel(Failover::prepareResponse(
      $userId,
      self::$failoverStrategy,
      get_class($exception)
    ));
  }

  private static function doNotTrackResponse($userId)
  {
    return new RestModel(Failover::prepareResponse(
      $userId,
      Failover::ALLOW,
      'Castle is set to do not track.'
    ));
  }

  private static function failoverUserId(array $attributes)
  {
    if (isset($attributes['user']) && is_array($attributes['user']) &&
        isset($attributes['user']['id'])) {
      return $attributes['user']['id'];
    }
    if (isset($attributes['matching_user_id'])) {
      return $attributes['matching_user_id'];
    }
    return null;
  }

  /**
   * Lists API
   */

  /**
   * Create a list
   * @param  Array $attributes 'name', 'color' and 'primary_field' are required
   * @return Array
   */
  public static function createList(array $attributes)
  {
    return self::sendRequest('post', '/lists', $attributes);
  }

  /**
   * Fetch all lists
   * @return Array
   */
  public static function getAllLists()
  {
    return self::sendRequest('get', '/lists');
  }

  /**
   * Fetch a single list
   * @param  String $listId
   * @return Array
   */
  public static function getList($listId)
  {
    return self::sendRequest('get', self::listPath($listId));
  }

  /**
   * Update a list
   * @param  String $listId
   * @param  Array  $attributes
   * @return Array
   */
  public static function updateList($listId, array $attributes)
  {
    return self::sendRequest('put', self::listPath($listId), $attributes);
  }

  /**
   * Delete a list
   * @param  String $listId
   * @return Array
   */
  public static function deleteList($listId)
  {
    return self::sendRequest('delete', self::listPath($listId));
  }

  /**
   * Query lists
   * @param  Array $attributes
   * @return Array
   */
  public static function queryList(array $attributes = array())
  {
    return self::sendRequest('post', '/lists/query', $attributes);
  }

  /**
   * List Items API
   */

  /**
   * Create a list item
   * @param  String $listId
   * @param  Array  $attributes 'author' and 'primary_value' are required
   * @return Array
   */
  public static function createListItem($listId, array $attributes)
  {
    return self::sendRequest('post', self::listItemsPath($listId), $attributes);
  }

  /**
   * Create a batch of list items
   * @param  String $listId
   * @param  Array  $attributes 'items' is required
   * @return Array
   */
  public static function createListItems($listId, array $attributes)
  {
    return self::sendRequest('post', self::listItemsPath($listId) . '/batch', $attributes);
  }

  /**
   * Fetch a list item
   * @param  String $listId
   * @param  String $itemId
   * @return Array
   */
  public static function getListItem($listId, $itemId)
  {
    return self::sendRequest('get', self::listItemPath($listId, $itemId));
  }

  /**
   * Update a list item
   * @param  String $listId
   * @param  String $itemId
   * @param  Array  $attributes 'comment' is required
   * @return Array
   */
  public static function updateListItem($listId, $itemId, array $attributes)
  {
    return self::sendRequest('put', self::listItemPath($listId, $itemId), $attributes);
  }

  /**
   * Query the items of a list
   * @param  String $listId
   * @param  Array  $attributes
   * @return Array
   */
  public static function queryListItems($listId, array $attributes = array())
  {
    return self::sendRequest('post', self::listItemsPath($listId) . '/query', $attributes);
  }

  /**
   * Count the items of a list
   * @param  String $listId
   * @param  Array  $attributes
   * @return Array
   */
  public static function countListItems($listId, array $attributes = array())
  {
    return self::sendRequest('post', self::listItemsPath($listId) . '/count', $attributes);
  }

  /**
   * Archive a list item
   * @param  String $listId
   * @param  String $itemId
   * @return Array
   */
  public static function archiveListItem($listId, $itemId)
  {
    return self::sendRequest('delete', self::listItemPath($listId, $itemId) . '/archive');
  }

  /**
   * Unarchive a list item
   * @param  String $listId
   * @param  String $itemId
   * @return Array
   */
  public static function unarchiveListItem($listId, $itemId)
  {
    return self::sendRequest('put', self::listItemPath($listId, $itemId) . '/unarchive');
  }

  /**
   * Privacy API
   */

  /**
   * Request the data stored for a user
   * @param  Array $attributes 'identifier' and 'identifier_type' are required
   * @return Array
   */
  public static function requestUserData(array $attributes)
  {
    return self::sendRequest('post', '/privacy/users', $attributes);
  }

  /**
   * Delete the data stored for a user
   * @param  Array $attributes 'identifier' and 'identifier_type' are required
   * @return Array
   */
  public static function deleteUserData(array $attributes)
  {
    return self::sendRequest('delete', '/privacy/users', $attributes);
  }

  /**
   * Events API (enterprise)
   */

  /**
   * Fetch the events schema
   * @return Array
   */
  public static function eventsSchema(array $attributes = array())
  {
    return self::sendRequest('get', '/events/schema', $attributes);
  }

  /**
   * Query events
   * @param  Array $attributes
   * @return Array
   */
  public static function queryEvents(array $attributes)
  {
    return self::sendRequest('post', '/events/query', $attributes);
  }

  /**
   * Group events
   * @param  Array $attributes
   * @return Array
   */
  public static function groupEvents(array $attributes)
  {
    return self::sendRequest('post', '/events/group', $attributes);
  }

  private static function sendRequest($method, $path, $attributes = null)
  {
    $request = new Request();
    list($response, $request) = $request->send($method, $path, $attributes);
    if ($request->rStatus == 204) {
      $response = array();
    }
    return $response;
  }

  private static function listPath($listId)
  {
    return '/lists/' . rawurlencode($listId);
  }

  private static function listItemsPath($listId)
  {
    return self::listPath($listId) . '/items';
  }

  private static function listItemPath($listId, $itemId)
  {
    return self::listItemsPath($listId) . '/' . rawurlencode($itemId);
  }
}
