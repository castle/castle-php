<?php

class Userbin_Error extends Exception
{

}

class Userbin_RequestError extends Userbin_Error
{

}

class Userbin_SecurityError extends Userbin_Error
{

}

class Userbin_ConfigurationError extends Userbin_Error
{

}

class Userbin_ApiError extends Userbin_Error
{
  public function __construct($type, $msg, $status)
  {
    parent::__construct($msg);
    $this->type = $type;
    $this->httpStatus = $status;
  }
}

class Userbin_BadRequest extends Userbin_ApiError
{

}


class Userbin_UnauthorizedError extends Userbin_ApiError
{

}

class Userbin_ForbiddenError extends Userbin_ApiError
{

}

class Userbin_NotFoundError extends Userbin_ApiError
{

}

class Userbin_UserUnauthorizedError extends Userbin_ApiError
{

}

class Userbin_InvalidParametersError extends Userbin_ApiError
{

}
