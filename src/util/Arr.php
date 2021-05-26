<?php

/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\util;

/**
 * Class Arr.
 */
class Arr
{
    public static function pack($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        return [$value];
    }
}
