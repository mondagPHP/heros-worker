<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DB
{
    public string $connection;

    /**
     * @param string $connection
     */
    public function __construct(string $connection = 'default')
    {
        $this->connection = $connection;
    }
}
