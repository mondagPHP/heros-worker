<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Contract;

use Workerman\Worker;

interface CronInterface
{
    public function onWorkerStart(?Worker $worker): void;
}
