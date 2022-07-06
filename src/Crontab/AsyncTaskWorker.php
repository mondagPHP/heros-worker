<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Crontab;

use Workerman\Connection\TcpConnection;

/**
 * 异步worker.
 */
class AsyncTaskWorker
{
    public function onMessage(TcpConnection $connection, string $data): void
    {
        $class = json_decode($data, true);
        if (isset($class['clazz'], $class['method']) && class_exists($class['clazz']) && method_exists($class['clazz'], $class['method'])) {
            call_user_func([new $class['clazz'](), $class['method']]);
        }
        $connection->send('ok');
    }
}
