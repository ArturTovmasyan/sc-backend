<?php

namespace  App\Model;

class JobStatus
{
    const TYPE_NOT_STARTED = 1;
    const TYPE_STARTED = 2;
    const TYPE_SUCCESS = 3;
    const TYPE_ERROR = 4;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_NOT_STARTED => 'Not Started',
        self::TYPE_STARTED => 'Started',
        self::TYPE_SUCCESS => 'Success',
        self::TYPE_ERROR => 'Error',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Not Started' => '1',
        'Started' => '2',
        'Success' => '3',
        'Error' => '4',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_NOT_STARTED => 1,
        self::TYPE_STARTED => 2,
        self::TYPE_SUCCESS => 3,
        self::TYPE_ERROR => 4,
    ];

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$types;
    }

    /**
     * @return array
     */
    public static function getTypeDefaultNames()
    {
        return self::$typeDefaultNames;
    }

    /**
     * @return array
     */
    public static function getTypeValues()
    {
        return self::$typeValues;
    }
}

