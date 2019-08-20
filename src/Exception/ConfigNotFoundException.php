<?php

namespace App\Exception;

use App\Model\ResponseCode;

class ConfigNotFoundException extends \RuntimeException
{
    /**
     * ConfigNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CONFIG_NOT_FOUND_EXCEPTION]['message'], ResponseCode::CONFIG_NOT_FOUND_EXCEPTION);
    }
}