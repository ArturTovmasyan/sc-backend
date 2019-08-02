<?php

namespace App\Exception;

use App\Model\ResponseCode;

class HelpCategoryNotFoundException extends \RuntimeException
{
    /**
     * HelpCategoryNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::HELP_CATEGORY_NOT_FOUND_EXCEPTION]['message'], ResponseCode::HELP_CATEGORY_NOT_FOUND_EXCEPTION);
    }
}