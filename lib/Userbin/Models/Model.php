<?php

class Userbin_Model
{
  protected $attributes   = array();

  protected $resourceName = null;

  protected $parent       = null;

  protected $idAttribute  = 'id';

  protected $perPage      = 30;

  protected $isSingular   = false;

  protected $stripPrefix  = 'userbin_';

  public function __construct($attributes=null)
  {
    //$this->setAttributes($attributes);
    if (is_array($attributes)) {
      $this->attributes = $attributes;
    }
    else if (isset($attributes)) {
      $this->setId($attributes);
    }
  }

  public function __get($key)
  {
    return $this->getAttribute($key);
  }

  public function __set($key, $value)
  {
    $this->setAttribute($key, $value);
  }

  protected function request($method, $path=null, $params=null)
  {
    if (isset($path) && substr($path, 0, 1) == '/') {
      $url = $path;
    }
    else {
      $path = isset($path) ? '/'.$path : $path;
      $url = $this->getResourcePath($path);
    }
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
    return null;
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

  public function getAttributes()
  {
    return $this->attributes;
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

  public function setSingular($isSingular)
  {
    $this->isSingular = !!$isSingular;
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
    $resource = self::snakeCase(get_class($this));
    # TODO: better pluralization
    if (!$this->isSingular) {
      $resource .= 's';
    }
    return str_replace($this->stripPrefix, '', $resource);
  }

  public function getResourcePath($extraPath = null)
  {
    $path = '';
    if (isset($this->parent)) {
      $path .= $this->parent->getResourcePath();
    }
    $path .= '/'.$this->getResourceName();
    $key = $this->getId();
    if (!$this->isSingular && isset($key)) {
      $path .= '/'.$key;
    }
    if (isset($extraPath)) {
      $path .= $extraPath;
    }
    return $path;
  }

  public function hasMany($model, $items=null)
  {
    $resource = new Userbin_Resource($model, $items);
    $resource->setParent($this);
    return $resource;
  }

  public function hasOne($model, $attributes=null)
  {
    $instance = new $model($attributes);
    $instance->setParent($this);
    $instance->setSingular(true);
    return $instance;
  }

  /*
   * CRUD
   */
  public function delete()
  {
    $this->request('delete');
  }

  public function fetch()
  {
    $attributes = $this->request('get');
    $this->setAttributes($attributes);
    return $this;
  }

  public function save()
  {
    $method = array_key_exists($this->idAttribute, $this->attributes) ? 'put' : 'post';
    $response = $this->request($method, null, $this->attributes);
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

  public function post($path=null, $params=null)
  {
    return $this->request('post', $path, $params);
  }

  public function get($path=null, $params=null)
  {
    return $this->request('get', $path, $params);
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

  public static function all($params=null)
  {
    $instance = new Userbin_Resource(get_called_class());
    return $instance->all($params);
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