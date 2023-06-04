<?php

namespace schema\model;

final class Registry
{
    private static $Registry = [];

    public static function set($sKey, $mVal)
    {
        self::$Registry[$sKey] = $mVal;
    }

    public static function get($sKey)
    {
        return self::$Registry[$sKey];
    }

    // public static function log()
    // {
    //     return::
    // }
}
?>