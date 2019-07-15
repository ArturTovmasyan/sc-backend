<?php

namespace App\Exception;

use App\Model\ResponseCode;

class JobNotFoundException extends \RuntimeException
{
    /**
     * JobNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::JOB_NOT_FOUND_EXCEPTION]['message'], ResponseCode::JOB_NOT_FOUND_EXCEPTION);
    }
}