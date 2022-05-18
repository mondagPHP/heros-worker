<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
use Framework\Annotation\Inject;

return [
    Inject::class => static function (ReflectionProperty $property, mixed $instance, \ReflectionAttribute $self) {
        $clazz = $property->getType()->getName();
        if (class_exists($clazz)) {
            $resource = container($clazz);
            $property->setAccessible(true);
            $property->setValue($instance, $resource);
        }
    },
];
