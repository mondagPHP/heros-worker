<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Contract;

use Workerman\Worker;

interface BootstrapInterface
{
    /**
     * onWorkerStart
     *
     * @param Worker $worker
     * @return void
     */
    public static function start(Worker $worker):void;
}
