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
        parent::__construct('', ResponseCode::CUSTOMER_NOT_FOUND_EXCEPTION);
    }
}