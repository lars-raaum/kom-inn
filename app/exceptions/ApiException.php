<?php

namespace app\exceptions;

class ApiException extends \app\Exception
{
    // Default Api error code is 400: Bad request
    protected $code = 400;
}
