<?php

namespace App\Util;


class ArrayUtil
{

    public static function remove_keys($array, $keys = array())
    {
        // If $keys is a comma-separated list, convert to an array.
        if (is_string($keys)) {
            $keys = explode(',', $keys);
        }

        // If array is empty or not an array at all, don't bother
        // doing anything else.
        if (empty($array) || (!is_array($array))) {
            return $array;
        }

        // array_diff_key() expected an associative array.
        $assocKeys = array();
        foreach ($keys as $key) {
            $assocKeys[$key] = true;
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::remove_keys($array[$key], $keys);
            }
        }

        return array_diff_key($array, $assocKeys);
    }

    public static function flatten($array, $keySeparator = '-')
    {
        if (is_array($array)) {
            foreach ($array as $name => $value) {
                $f = self::flatten($value, $keySeparator);
                if (is_array($f)) {
                    foreach ($f as $key => $val) {
                        $array[$name . $keySeparator . $key] = $val;
                    }

                    unset($array[$name]);
                }
            }
        }

        return $array;
    }
}
