<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Queue;

use Workerman\RedisQueue\Client as RedisClient;

/**
 * Class Client.
 * @method static void send(string $queue, array $data, int $delay = 0)
 */
class Client
{
    /**
     * @var Client[]
     */
    protected static ?array $_connections = null;

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::connection('default')->{$name}(...$arguments);
    }

    /**
     * @param string $name
     * @return RedisClient
     */
    public static function connection(string $name = 'default'): RedisClient
    {
        if (! isset(static::$_connections[$name])) {
            $config = config('redis_queue', []);
            if (! isset($config[$name])) {
                throw new \RuntimeException("RedisQueue connection {$name} not found");
            }
            $host = $config[$name]['host'];
            $options = $config[$name]['options'];
            $client = new RedisClient($host, $options);
            static::$_connections[$name] = $client;
        }
        return static::$_connections[$name];
    }
}
