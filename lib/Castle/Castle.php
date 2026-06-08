<?php

namespace Castle;

abstract class Castle
{
  const VERSION = '4.0.0';

  const HEADER_COOKIE = 'Cookie';
  const HEADER_USER_AGENT = 'User-Agent';

  public static $apiKey;

  public static $apiBase = 'https://api.castle.io';

  public static $apiVersion = 'v1';

  public static $tokenStore = 'Castle_TokenStore';

  public static $cookieStore = 'Castle\\CookieStore';

  public static $scrubHeaders = array(self::HEADER_COOKIE);

  private static $useAllowlist = false;
  public static $allowlistedHeaders = array(self::HEADER_USER_AGENT);

  private static $curlOpts = array();
  private static $validCurlOpts = array(CURLOPT_CONNECTTIMEOUT,
                                        CURLOPT_CONNECTTIMEOUT_MS,
                                        CURLOPT_TIMEOUT,
                                        CURLOPT_TIMEOUT_MS);

  public static function getApiKey()
  {
    return self::$apiKey;
  }

  public static function setApiKey($apiKey)
  {
    self::$apiKey = $apiKey;
  }

  public static function setCurlOpts($curlOpts)
  {
    $invalidOpts = array_diff(array_keys($curlOpts), self::$validCurlOpts);
    // If any options are invalid.
    if (count($invalidOpts)) {
      // Throw an exception listing all invalid options.
      throw new CurlOptionError('These cURL options are not allowed:' .
                                join(',', $invalidOpts));
    }
    // May seem odd, but one may want the option of stripping them out, and so
    // would probably simply use error_log instead of throw.
    self::$curlOpts = array_diff($curlOpts, array_flip($invalidOpts));
  }

  public static function getCurlOpts()
  {
    return self::$curlOpts;
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

  public static function getApiVersion()
  {
    return self::$apiVersion;
  }

  public static function setApiVersion($apiVersion)
  {
    self::$apiVersion = $apiVersion;
  }

  public static function getCookieStore()
  {
    return new self::$cookieStore;
  }

  public static function getTokenStore()
  {
    return new self::$tokenStore(self::getCookieStore());
  }

  public static function setTokenStore($serializerClass)
  {
    self::$tokenStore = $serializerClass;
  }


  /**
   * Filter an action
   * @param  String $attributes 'request_token', 'event', 'context' are required, 'user' with 'id' and 'properties' are optional
   * @return RestModel
   */
  public static function filter(array $attributes)
  {
    $request = new Request();
    list($response, $request) = $request->send('post', '/filter', $attributes);
    if ($request->rStatus == 204) {
      $response = array();
    }
    return new RestModel($response);
  }

  /**
   * Log events
   * @param  String $attributes 'request_token', 'event', 'status' and 'user' object with 'id' are required
   * @return None
   */
  public static function log(array $attributes)
  {
    $request = new Request();
    $request->send('post', '/log', $attributes);
  }

  /**
   * Risk
   * @param  String $attributes 'request_token', 'event', 'context', 'user' with 'id' are required, 'status', 'properties' are optional
   * @return RestModel
   */
  public static function risk(array $attributes)
  {
    $request = new Request();
    list($response, $request) = $request->send('post', '/risk', $attributes);
    if ($request->rStatus == 204) {
      $response = array();
    }
    return new RestModel($response);
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
