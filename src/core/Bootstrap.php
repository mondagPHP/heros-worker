<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\core;

use Workerman\Worker;

interface Bootstrap
{
    /**
     * onWorkerStart.
     *
     * @param $worker Worker
     * @return mixed
     */
    public static function start(Worker $worker);
}
