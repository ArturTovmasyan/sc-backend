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
        parent::__construct('', ResponseCode::JOB_NOT_FOUND_EXCEPTION);
    }
}