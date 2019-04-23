<?php

namespace App\Exception;

use App\Model\ResponseCode;

class VhostNotFoundException extends \RuntimeException
{
    /**
     * VhostNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::VHOST_NOT_FOUND_EXCEPTION);
    }
}