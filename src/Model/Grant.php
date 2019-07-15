<?php

namespace App\Model;


class Grant
{
    public static $IDENTITY_ALL = 0;
    public static $IDENTITY_SEVERAL = 1;
    public static $IDENTITY_OWN = 2;

    public static $LEVEL_NONE = 0;
    public static $LEVEL_VIEW = 1;
    public static $LEVEL_EDIT = 2;
    public static $LEVEL_CREATE = 3;
    public static $LEVEL_DELETE = 4;
    public static $LEVEL_UNDELETE = 5;


    public static function str2identity(?string $identity) : int
    {
        switch ($identity) {
            case "ALL":
                return self::$IDENTITY_ALL;
            case "SEVERAL":
                return self::$IDENTITY_SEVERAL;
            case "OWN":
                return self::$IDENTITY_OWN;
            default:
                return self::$IDENTITY_ALL;
        }
    }

    public static function str2level(?string $level) : int
    {
        switch ($level) {
            case "NONE":
                return self::$LEVEL_NONE;
            case "VIEW":
                return self::$LEVEL_VIEW;
            case "EDIT":
                return self::$LEVEL_EDIT;
            case "CREATE":
                return self::$LEVEL_CREATE;
            case "DELETE":
                return self::$LEVEL_DELETE;
            case "UNDELETE":
                return self::$LEVEL_UNDELETE;
            default:
                return self::$LEVEL_NONE;
        }
    }
}