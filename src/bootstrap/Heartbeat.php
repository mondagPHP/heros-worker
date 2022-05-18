<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\bootstrap;

use framework\core\Bootstrap;
use framework\database\HeroDB;
use Workerman\Lib\Timer;
use Workerman\Worker;

/**
 * Class Heartbeat.
 */
class Heartbeat implements Bootstrap
{
    public static function start(Worker $worker)
    {
        $database = config('database', []);
        if (HEARTBEAT_TIME <= 0) {
            return;
        }
        Timer::add(HEARTBEAT_TIME, function () use ($database) {
            foreach ($database ?? [] as $connectionName => $value) {
                //默认没有配置也是加入心跳
                if (isset($value['is_beat']) && $value['is_beat']) {
                    HeroDB::connection($connectionName)->select('select 1 limit 1');
                }
            }
        });
    }
}