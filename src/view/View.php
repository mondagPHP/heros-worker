<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\view;

/**
 * Class View.
 */
class View
{
    public static function assign(string $name, $value = null)
    {
        static $handler;
        $handler = $handler ?: config('view.handler');
        $handler::assign($name, $value);
    }
}
