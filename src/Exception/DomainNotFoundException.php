<?php

namespace App\Exception;

use App\Model\ResponseCode;

class DomainNotFoundException extends \RuntimeException
{
    /**
     * DomainNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DOMAIN_NOT_FOUND_EXCEPTION]['message'], ResponseCode::DOMAIN_NOT_FOUND_EXCEPTION);
    }
}