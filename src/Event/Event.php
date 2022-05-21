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
    protected static Dispatcher $instance;

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
     * @return Dispatcher
     */
    public static function instance(): Dispatcher
    {
        if (!isset(static::$instance)) {
            static::$instance = new Dispatcher((new Container));
            $eventsList = config('events');
            if (isset($eventsList['listener']) && !empty($eventsList['listener'])) {
                foreach ($eventsList['listener'] as $event => $listener) {
                    if (!is_array($listener)) {
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
