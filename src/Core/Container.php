<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Core;

/**
 * Class Container
 *
 * @method static mixed get($name)
 * @method static mixed make($name, array $parameters)
 * @method static bool has($name)
 */
class Container
{
    protected static IOC $_instance;

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(...$arguments);
    }

    /**
     * @return IOC
     */
    public static function instance(): IOC
    {
        if (! isset(static::$_instance)) {
            static::$_instance = new IOC();
        }

        return static::$_instance;
    }
}
