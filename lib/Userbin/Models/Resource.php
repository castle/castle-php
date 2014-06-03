<?php

class Userbin_Resource
{
  public function __construct($model, $parent=null)
  {
    $this->model  = $model;
    $this->parent = $parent;
  }

  protected function createModel($attributes=null)
  {
    $instance = new $this->model($attributes);
    if (isset($this->parent)) {
      $instance->setParent($this->parent);
    }
    return $instance;
  }

  public function all()
  {
    // TODO
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