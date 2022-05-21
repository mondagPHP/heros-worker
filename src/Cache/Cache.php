<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Cache;

use Framework\Redis\Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Class Cache
 * @package Framework\Cache
 * @method static mixed get($key, $default = null)
 * @method static bool set($key, $value, $ttl = null)
 * @method static bool delete($key)
 * @method static bool has($key)
 * @method static bool clear()
 */
class Cache
{
    /**
     * @var Psr16Cache $_instance
     */
    protected static Psr16Cache $_instance;

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
     * @return Psr16Cache
     */
    public static function instance(): Psr16Cache
    {
        if (! isset(static::$_instance)) {
            $adapter = new RedisAdapter(Redis::connection()->client());
            static::$_instance = new Psr16Cache($adapter);
        }
        return static::$_instance;
    }
}
