<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Bootstrap;

use Framework\Contract\BootstrapInterface;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Workerman\Worker;

/**
 * Class LaravelDB
 */
class LaravelDB implements BootstrapInterface
{
    public static function start(?Worker $worker): void
    {
        if (! class_exists(Capsule::class)) {
            return;
        }
        $capsule = new Capsule();
        $configs = config('database', []);
        foreach ($configs ?? [] as $name => $config) {
            $capsule->addConnection($config, $name);
        }
        if (class_exists(Dispatcher::class)) {
            $capsule->setEventDispatcher(new Dispatcher(new Container()));
        }
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
