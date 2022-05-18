<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Crontab;

use Framework\Component\StdoutLogger;
use Framework\Contract\CronInterface;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Worker;

/**
 * 采用异步通知消息来完成定时任务
 * 主要解决是如果定时任务定义间隙过短、任务执行过久，导致部分任务跳过。
 * 参考[http://doc.workerman.net/faq/async-task.html].
 */
class CrontabTask implements CronInterface
{
    // 定时任务列表 memo:定时任务的备注，该属性为可选属性，没有任何逻辑上的意义，仅供开发人员查阅帮助对该定时任务的理解。
    protected static array $cronList = [];

    /**
     * @param Worker|null $worker
     * @return void
     */
    public function onWorkerStart(?Worker $worker): void
    {
        foreach (static::$cronList ?? [] as $cron) {
            new Crontab($cron['rule'], static function () use ($cron) {
                static::delivery($cron['task'][0], $cron['task'][1], $cron['memo']);
            });
        }
    }

    /**
     * 投递到异步进程.
     * @throws \Exception
     */
    private static function delivery(string $clazz, string $method, string $memo): void
    {
        $taskConnection = new AsyncTcpConnection(config('app.async_worker'));
        $lock = new CrontabSingleLock("{$clazz}{$method}");
        try {
            $lock->tryLock();
            $taskConnection->send(json_encode(['clazz' => $clazz, 'method' => $method]));
            $taskConnection->onMessage = function (AsyncTcpConnection $asyncTcpConnection, $taskResult) use ($memo) {
                (new StdoutLogger())->debug("定时任务:{$memo},执行结果:{$taskResult}!");
                $asyncTcpConnection->close();
            };
            $taskConnection->connect();
        } finally {
            $lock->unLock();
        }
    }
}
