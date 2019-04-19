<?php

namespace App\Exception;

use App\Model\ResponseCode;

class UserNotFoundException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::USER_NOT_FOUND_EXCEPTION);
    }
}