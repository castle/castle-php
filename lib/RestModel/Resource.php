<?php

class Castle_Resource
{
  protected $parent = null;

  protected $items  = null;

  public function __construct($model, $items=null)
  {
    $this->model = $model;
    if (is_array($items)) {
      $this->loadItems($items);
    }
  }

  public function __call($method, $arguments)
  {
    /* Check if there is an instance method */
    $instance = $this->createModel();
    if (method_exists($instance, $method)) {
      $id = array_shift($arguments);
      $instance->setId($id);
      return call_user_func_array(array($instance, $method), $arguments);
    }
    else {
      trigger_error('Call to undefined method: '.$method);
    }
  }

  protected function createModel($attributes=null)
  {
    $instance = new $this->model($attributes);
    if (isset($this->parent)) {
      $instance->setParent($this->parent);
    }
    return $instance;
  }

  protected function loadItems(array $items=array())
  {
    $this->items = array();
    foreach ($items as $attributes) {
      $this->items[] = $this->createModel($attributes);
    }
  }

  public function setParent($parent)
  {
    $this->parent = $parent;
  }

  public function all($params=null)
  {
    if (is_array($this->items)) {
      return $this->items;
    }
    $instance = $this->createModel();
    $items = $instance->get();
    $this->loadItems($items);
    return $this->items;
  }

  public function create($attributes=null)
  {
    $instance = $this->createModel($attributes);
    return $instance->save();
  }

  public function destroy($id=null)
  {
    $instance = $this->createModel($id);
    return $instance->delete();
  }

  public function find($id)
  {
    $instance = $this->createModel($id);
    return $instance->fetch();
  }
}
