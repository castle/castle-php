<?php

class Userbin_Model
{
  protected $attributes   = array();

  protected $resourceName = null;

  protected $primaryKey   = 'id';

  protected $perPage      = 30;

  protected $stripPrefix  = 'userbin_';

  public function __construct(array $attributes = array())
  {
    $this->setAttributes($attributes);
  }

  public function __get($key)
  {
    return $this->getAttribute($key);
  }

  public function __set($key, $value)
  {
    $this->setAttribute($key, $value);
  }

  protected function makeRequest($method, $extraPath=null, $params=null)
  {
    $url = $this->getRequestPath($extraPath);
    $request = new Userbin_Request();
    list($response, $request) = $request->send($method, $url, $params);
    return $response;
  }

  public function getAttribute($key)
  {
    if (array_key_exists($key, $this->attributes))
    {
      return $this->attributes[$key];
    }
  }

  public function setAttribute($key, $value)
  {
    $this->attributes[$key] = $value;
  }

  public function setAttributes($attributes)
  {
    if (!isset($attributes)) return;
    foreach ($attributes as $key => $value) {
      $this->setAttribute($key, $value);
    }
  }


  public function getPerPage()
  {
    return $this->perPage;
  }

  public function setPerPage($perPage)
  {
    $this->perPage = $perPage;
  }

  public function getResourceName()
  {
    if (isset($this->resourceName)) return $this->resourceName;
    # TODO: better pluralization
    return str_replace($this->stripPrefix, '', self::snakeCase(get_class($this)).'s');
  }

  public function getRequestPath($extraPath = null)
  {
    $path = '/'.$this->getResourceName();
    $key = $this->getAttribute($this->primaryKey);
    if (isset($key)) {
      $path .= '/'.$key;
    }
    if (isset($extraPath)) {
      $path .= $extraPath;
    }
    return $path;
  }

  /*
   * CRUD
   */
  public function delete()
  {
    $this->makeRequest('delete');
  }

  public function fetch()
  {
    $attributes = $this->makeRequest('get');
    $this->setAttributes($attributes);
    return $this;
  }

  public function save()
  {
    $this->makeRequest('put', null, $this->attributes);
    return $this;
  }

  public function update(array $attributes = array())
  {
    # code...
    return $this;
  }

  /*
   * REST
   */

  public function post($path, $params=null)
  {
    return $this->makeRequest('post', $path, $params);
  }

  /*
   * Static
   */

  public static function snakeCase($input) {
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
      $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
  }

  public static function all()
  {
    # code...
  }

  public static function create($attributes)
  {
    $instance = new static;
    $response = $instance->makeRequest('post', null, $attributes);
    $instance->setAttributes($response);
    return $instance;
  }


  public static function destroy($id)
  {
    $instance = new static;
    $instance->setAttribute($instance->primaryKey, $id);
    $instance->delete();
  }

  public static function find($id)
  {
    $instance = new static;
    $instance->setAttribute($instance->primaryKey, $id);
    return $instance->fetch();
  }
}

?>