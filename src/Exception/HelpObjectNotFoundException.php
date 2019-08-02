<?php

namespace App\Exception;

use App\Model\ResponseCode;

class HelpObjectNotFoundException extends \RuntimeException
{
    /**
     * HelpObjectNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::HELP_OBJECT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::HELP_OBJECT_NOT_FOUND_EXCEPTION);
    }
}