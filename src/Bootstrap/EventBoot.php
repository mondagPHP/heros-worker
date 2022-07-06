<?php
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Bootstrap;

use Framework\Contract\BootstrapInterface;
use Framework\Core\Log;
use Framework\Event\Event;
use Workerman\Worker;

class EventBoot implements BootstrapInterface
{
    /**
     * @var array
     */
    protected static array $events = [];

    public static function start(?Worker $worker): void
    {
        static::getEvents(config('event', []));
    }

    protected static function convertCallable($callback)
    {
        if (\is_array($callback)) {
            $callback = \array_values($callback);
            if (isset($callback[1]) && \is_string($callback[0]) && \class_exists($callback[0])) {
                $callback = [\container($callback[0]), $callback[1]];
            }
        }

        return $callback;
    }

    /**
     * @param $configs
     * @return void
     */
    protected static function getEvents($configs)
    {
        $events = [];
        foreach ($configs as $eventName => $callbacks) {
            $callbacks = static::convertCallable($callbacks);
            if (is_callable($callbacks)) {
                $events[$eventName] = [$callbacks];
                Event::on($eventName, $callbacks);
                continue;
            }
            if (! is_array($callbacks)) {
                $msg = "Events: $eventName => ".var_export($callbacks, true)." is not callable\n";
                echo $msg;
                Log::error($msg);
                continue;
            }
            foreach ($callbacks as $callback) {
                $callback = static::convertCallable($callback);
                if (is_callable($callback)) {
                    $events[$eventName][] = $callback;
                    Event::on($eventName, $callback);
                    continue;
                }
                $msg = "Events: $eventName => ".var_export($callback, true)." is not callable\n";
                echo $msg;
                Log::error($msg);
            }
        }
        static::$events = array_merge_recursive(static::$events, $events);
    }
}
