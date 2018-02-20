<?php

class Castle_Error extends Exception
{

}

class Castle_RequestError extends Castle_Error
{

}

class Castle_ConfigurationError extends Castle_Error
{

}

class Castle_CurlOptionError extends Castle_Error
{

}

class Castle_ApiError extends Castle_Error
{
  public function __construct($msg, $type = null, $status = null)
  {
    parent::__construct($msg);
    $this->type = $type;
    $this->httpStatus = $status;
  }
}

class Castle_BadRequest extends Castle_ApiError
{

}

class Castle_UnauthorizedError extends Castle_ApiError
{

}

class Castle_ForbiddenError extends Castle_ApiError
{

}

class Castle_NotFoundError extends Castle_ApiError
{

}

class Castle_InvalidParametersError extends Castle_ApiError
{

}
