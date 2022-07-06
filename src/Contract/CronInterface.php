<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Contract;

use Workerman\Worker;

interface CronInterface
{
    public function onWorkerStart(?Worker $worker): void;
}
