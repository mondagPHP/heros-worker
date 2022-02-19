<?php

declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
use Framework\Annotation\Inject;

return [
    Inject::class => function (ReflectionProperty $property, mixed $instance, \ReflectionAttribute $self) {
        $clazz = $property->getType()->getName();
        if (class_exists($clazz)) {
            $resource = container()->get($clazz);
            $property->setAccessible(true);
            $property->setValue($instance, $resource);
        }
    },
];
