<?php

namespace App\Service;

class ValidJSONStructure
{
    public static function existKeys(array &$arr, ...$keys): bool
    {
        return array_all($keys, function (string $key) use (&$arr) {
            return isset($arr[$key]);
        });
    }

    public static function checkKeys(array &$arr, ...$keys): ?string
    {
        return array_find($keys, function (string $key) use (&$arr) {
            return !isset($arr[$key]);
        });
    }
}