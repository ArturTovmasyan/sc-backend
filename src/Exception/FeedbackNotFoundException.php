<?php

namespace App\Exception;

use App\Model\ResponseCode;

class FeedbackNotFoundException extends \RuntimeException
{
    /**
     * FeedbackNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::FEEDBACK_NOT_FOUND_EXCEPTION]['message'], ResponseCode::FEEDBACK_NOT_FOUND_EXCEPTION);
    }
}