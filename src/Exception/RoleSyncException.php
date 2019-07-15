<?php

namespace App\Exception;

use App\Model\ResponseCode;

class RoleSyncException extends \RuntimeException
{
    /**
     * RoleSyncException constructor.
     * @param string $message
     */
    public function __construct($message = '')
    {
        parent::__construct($message, ResponseCode::ROLE_SYNC_ERROR);
    }
}