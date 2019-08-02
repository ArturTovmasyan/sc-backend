<?php

namespace  App\Model;

class HelpObjectType
{
    const TYPE_PDF   = 1;
    const TYPE_VIDEO = 2;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_PDF => 'PDF',
        self::TYPE_VIDEO => 'Video',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'PDF' => self::TYPE_PDF,
        'Video' => self::TYPE_VIDEO,
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_PDF => 1,
        self::TYPE_VIDEO => 2,
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

