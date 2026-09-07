<?php

namespace Castle;

class Error extends \Exception
{

}

class RequestError extends Error
{

}

class ConfigurationError extends Error
{

}

class ApiError extends Error
{
  public $type;
  public $httpStatus;

  public function __construct($msg = '', $type = null, $status = null)
  {
    parent::__construct($msg === null ? '' : $msg);
    $this->type = $type;
    $this->httpStatus = $status;
  }
}

class InternalServerError extends ApiError
{

}

class BadRequest extends ApiError
{

}

class UnauthorizedError extends ApiError
{

}

class ForbiddenError extends ApiError
{

}

class NotFoundError extends ApiError
{

}

class InvalidParametersError extends ApiError
{

}

class InvalidRequestTokenError extends InvalidParametersError
{

}

class PaymentRequiredError extends ApiError
{

}

class WebhookVerificationError extends Error
{

}
