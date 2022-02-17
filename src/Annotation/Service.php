<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */

namespace Framework\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Service
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
