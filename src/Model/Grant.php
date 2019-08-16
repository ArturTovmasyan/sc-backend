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


    public static function identity2str(?int $identity) : string
    {
        switch ($identity) {
            case self::$IDENTITY_SEVERAL:
                return "SEVERAL";
            case self::$IDENTITY_OWN:
                return "OWN";
            case self::$IDENTITY_ALL:
            default:
                return "ALL";
        }
    }

    public static function str2identity(?string $identity) : int
    {
        switch ($identity) {
            case "SEVERAL":
                return self::$IDENTITY_SEVERAL;
            case "OWN":
                return self::$IDENTITY_OWN;
            case "ALL":
            default:
                return self::$IDENTITY_ALL;
        }
    }

    public static function level2str(?int $level) : string
    {
        switch ($level) {
            case self::$LEVEL_VIEW:
                return "VIEW";
            case self::$LEVEL_EDIT:
                return "EDIT";
            case self::$LEVEL_CREATE:
                return "CREATE";
            case self::$LEVEL_DELETE:
                return "DELETE";
            case self::$LEVEL_UNDELETE:
                return"UNDELETE";
            case self::$LEVEL_NONE:
            default:
                return "NONE";
        }
    }

    public static function str2level(?string $level) : int
    {
        switch ($level) {
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
            case "NONE":
            default:
                return self::$LEVEL_NONE;
        }
    }
}