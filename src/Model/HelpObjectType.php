<?php

namespace  App\Model;

class HelpObjectType
{
    const TYPE_PDF   = 1;
    const TYPE_VIDEO = 2;
    const TYPE_VIMEO = 3;
    const TYPE_YOUTUBE = 4;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_PDF => 'PDF',
        self::TYPE_VIDEO => 'Video',
        self::TYPE_VIMEO => 'Vimeo',
        self::TYPE_YOUTUBE => 'Youtube',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'PDF' => self::TYPE_PDF,
        'Video' => self::TYPE_VIDEO,
        'Vimeo' => self::TYPE_VIMEO,
        'Youtube' => self::TYPE_YOUTUBE,
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_PDF => 1,
        self::TYPE_VIDEO => 2,
        self::TYPE_VIMEO => 3,
        self::TYPE_YOUTUBE => 4,
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

