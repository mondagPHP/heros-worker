<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\bootstrap;

use framework\core\Bootstrap;
use Monolog\Logger;
use Workerman\Worker;

/**
 * @method static void log($level, $message, array $context = [])
 * @method static void debug($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void emergency($message, array $context = [])
 *                                                                Class Log
 */
class Log implements Bootstrap
{
    /**
     * @var array
     */
    protected static $instance = [];

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::channel('default')->{$name}(...$arguments);
    }

    public static function start(Worker $worker): void
    {
        $configs = config('log', []);
        foreach ($configs ?? [] as $channel => $config) {
            $logger = static::$instance[$channel] = new Logger($channel);
            foreach ($config['handlers'] ?? [] as $handlerConfig) {
                $handler = new $handlerConfig['class'](...array_values($handlerConfig['constructor']));
                if (isset($handlerConfig['formatter'])) {
                    $formatter = new $handlerConfig['formatter']['class'](...array_values($handlerConfig['formatter']['constructor']));
                    $handler->setFormatter($formatter);
                }
                $logger->pushHandler($handler);
            }
        }
    }

    /**
     * @param string $name
     * @return Logger
     */
    public static function channel(string $name = 'default'): ?Logger
    {
        return static::$instance[$name] ?? null;
    }
}
