<?php

namespace App\Exception;

use App\Model\ResponseCode;

class InvalidGrantConfigException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INVALID_GRANT_CONFIG]['message'], ResponseCode::INVALID_GRANT_CONFIG);
    }
}