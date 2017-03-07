<?php

namespace app\exceptions;

class ServiceException extends \app\Exception
{
    // Default service response code is 500
    protected $code = 500;
}
