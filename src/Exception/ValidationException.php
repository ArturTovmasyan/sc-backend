<?php

namespace App\Exception;

use App\Model\ResponseCode;

class ValidationException extends \RuntimeException
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * ValidationException constructor.
     * @param array $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('', ResponseCode::VALIDATION_ERROR_EXCEPTION);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}