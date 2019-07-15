<?php

namespace App\Exception;

use App\Model\ResponseCode;

class CustomerNotFoundException extends \RuntimeException
{
    /**
     * CustomerNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CUSTOMER_NOT_FOUND_EXCEPTION]['message'], ResponseCode::CUSTOMER_NOT_FOUND_EXCEPTION);
    }
}