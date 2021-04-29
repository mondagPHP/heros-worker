<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\queue\redis;

/**
 * Interface Consumer.
 */
interface Consumer
{
    public function consume($data);
}
