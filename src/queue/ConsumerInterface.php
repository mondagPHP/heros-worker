<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace Framework\Queue;

interface ConsumerInterface
{
    public function consume(array $data): void;
}
