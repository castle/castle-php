<?php

class Userbin_Model
{
  protected $attributes   = array();

  protected $resourceName = null;

  protected $parent       = null;

  protected $idAttribute  = 'id';

  protected $perPage      = 30;

  protected $stripPrefix  = 'userbin_';

  public function __construct($attributes = array())
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
    $url = $this->getResourcePath($extraPath);
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

  public function getId()
  {
    return $this->getAttribute($this->idAttribute);
  }

  public function setId($id)
  {
    $this->setAttribute($this->idAttribute, $id);
  }

  public function setAttributes($attributes)
  {
    if (!isset($attributes)) return;
    foreach ($attributes as $key => $value) {
      $this->setAttribute($key, $value);
    }
  }

  public function setParent(Userbin_Model $parent)
  {
    $this->parent = $parent;
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

  public function getResourcePath($extraPath = null)
  {
    $path = '';
    if (isset($this->parent)) {
      $path .= $this->parent->getResourcePath();
    }
    $path .= '/'.$this->getResourceName();
    $key = $this->getId();
    if (isset($key)) {
      $path .= '/'.$key;
    }
    if (isset($extraPath)) {
      $path .= $extraPath;
    }
    return $path;
  }

  public function hasResource($model)
  {
    return new Userbin_Resource($model, $this);
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
    $method = array_key_exists($this->idAttribute, $this->attributes) ? 'put' : 'post';
    $response = $this->makeRequest($method, null, $this->attributes);
    if (!is_array($response)) {
      throw new Userbin_Error('Invalid response');
    }
    $this->setAttributes($response);
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
    $instance = new Userbin_Resource(get_called_class());
    return $instance->all();
  }

  public static function create($attributes=null)
  {
    $instance = new Userbin_Resource(get_called_class());
    return $instance->create($attributes);
  }


  public static function destroy($id)
  {
    $instance = new Userbin_Resource(get_called_class());
    return $instance->destroy($id);
  }

  public static function find($id)
  {
    $instance = new Userbin_Resource(get_called_class());
    return $instance->find($id);
  }
}

?>