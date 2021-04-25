<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\lock;

interface ISynLock
{
    /**
     * 获取同步锁
     */
    public function tryLock(): bool;

    /**
     * 解锁
     */
    public function unLock(): bool;
}
