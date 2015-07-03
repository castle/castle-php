<?php

namespace Castle\Errors;

class ApiError extends CastleException
{
    public function __construct($msg, $type = null, $status = null)
    {
        parent::__construct($msg);
        $this->type = $type;
        $this->httpStatus = $status;
    }
}