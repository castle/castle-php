<?php

class Userbin_Resource
{
  protected $parent = null;

  protected $items  = null;

  public function __construct($model, $items=null)
  {
    $this->model = $model;
    if (is_array($items)) {
      $this->items = $this->oadItems($items);
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

  public function destroy($id)
  {
    $instance = new $this->model;
    $instance->setId($id);
    return $instance->delete();
  }

  public function find($id)
  {
    $instance = new $this->model;
    $instance->setId($id);
    return $instance->fetch();
  }
}