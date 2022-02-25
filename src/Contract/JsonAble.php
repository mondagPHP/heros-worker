<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Contract;

interface JsonAble
{
    public function toJson(): string;
}
