<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace Framework\Core;

/**
 * Class Container
 * @package support
 * @method static mixed get($name)
 * @method static mixed make($name, array $parameters)
 * @method static bool has($name)
 */
class Container
{
    protected static $_instance = null;

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(... $arguments);
    }

    /**
     * @return Container|null
     */
    public static function instance()
    {
        if (! static::$_instance) {
            static::$_instance = new \Monda\Utils\Container\Container();
        }
        return static::$_instance;
    }
}
