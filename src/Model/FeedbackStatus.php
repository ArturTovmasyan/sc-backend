<?php

namespace  App\Model;

class FeedbackStatus
{
    const NEW = 1;
    const REVIEW = 2;
    const CLOSED = 3;

    /**
     * @var array
     */
    private static $types = [
        self::NEW => 'New',
        self::REVIEW => 'Review',
        self::CLOSED => 'Closed',
    ];

    /**Disable
Disable
     * @var array
     */
    private static $typeDefaultNames = [
        'New' => '1',
        'Review' => '2',
        'Closed' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::NEW => 1,
        self::REVIEW => 2,
        self::CLOSED => 3,
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

