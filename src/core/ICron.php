<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\core;

interface ICron
{
    public function onWorkerStart();
}
