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
        parent::__construct(ResponseCode::$titles[ResponseCode::VHOST_NOT_FOUND_EXCEPTION]['message'], ResponseCode::VHOST_NOT_FOUND_EXCEPTION);
    }
}