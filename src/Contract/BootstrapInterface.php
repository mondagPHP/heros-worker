<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Contract;

use Workerman\Worker;

interface BootstrapInterface
{
    /**
     * onWorkerStart
     *
     * @param Worker|null $worker
     * @return void
     */
    public static function start(?Worker $worker):void;
}
