<?php

namespace App\Exception;

use App\Model\ResponseCode;

class RoleNotFoundException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ROLE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::ROLE_NOT_FOUND_EXCEPTION);
    }
}