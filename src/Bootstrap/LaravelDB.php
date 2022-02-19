<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Bootstrap;

use Framework\Contract\BootstrapInterface;
use Framework\Core\Log;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;
use Workerman\Worker;

/**
 * Class LaravelDB
 * @package Framework\Bootstrap
 */
class LaravelDB implements BootstrapInterface
{
    public static function start(Worker $worker): void
    {
        if (! class_exists('\Illuminate\Database\Capsule\Manager')) {
            return;
        }
        $capsule = new Capsule();
        $configs = config('database', []);
        foreach ($configs ?? [] as $name => $config) {
            $capsule->addConnection($config, $name);
        }
        if (class_exists('\Illuminate\Events\Dispatcher')) {
            $capsule->setEventDispatcher(new Dispatcher(new Container()));
        }
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        if (config('app.debug', true)) {
            //添加监听事件
            $capsule->setEventDispatcher(new Dispatcher(new Container()));
            /** @var Dispatcher $dispatcher */
            $dispatcher = $capsule->getEventDispatcher();
            if (! $dispatcher->hasListeners(QueryExecuted::class)) {
                $dispatcher->listen(QueryExecuted::class, function ($query) {
                    $sql = vsprintf(str_replace('?', "'%s'", $query->sql), $query->bindings) . " \t[" . $query->time . ' ms] ';
                    Log::debug($sql);
                });
            }
        }
    }
}
