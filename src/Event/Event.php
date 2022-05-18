<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Event;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;

/**
 *  class Event
 * @package support
 *  Strings methods
 * @method static Dispatcher dispatch($event)
 */
class Event
{
    /**
     * @var Dispatcher
     */
    protected static $instance;

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::instance()->{$name}(... $arguments);
    }

    /**
     * @return Dispatcher|null
     */
    public static function instance()
    {
        if (! static::$instance) {
            $container = new Container;
            static::$instance = new Dispatcher($container);
            $eventsList = config('events');
            if (isset($eventsList['listener']) && ! empty($eventsList['listener'])) {
                foreach ($eventsList['listener'] as $event => $listener) {
                    if (! is_array($listener)) {
                        continue;
                    }
                    foreach ($listener as $l) {
                        static::$instance->listen($event, $l);
                    }
                }
            }
        }
        return static::$instance;
    }
}
