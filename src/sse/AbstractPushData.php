<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\sse;

/**
 * Class AbstractPushData
 * @package framework\sse
 */
abstract class AbstractPushData
{
    /**
     * @var int 记录连接时间
     */
    public $start;

    /**
     * @return array
     */
    abstract public function pushData(): array;
}
