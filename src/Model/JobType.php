<?php

namespace  App\Model;

class JobType
{
    const TYPE_CREATE = 1;
    const TYPE_ENABLE = 2;
    const TYPE_DISABLE = 3;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_CREATE => 'Create',
        self::TYPE_ENABLE => 'Enable',
        self::TYPE_DISABLE => 'Disable',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Create' => '1',
        'Enable' => '2',
        'Disable' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_CREATE => 1,
        self::TYPE_ENABLE => 2,
        self::TYPE_DISABLE => 3,
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

