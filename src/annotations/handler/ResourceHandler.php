<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
use framework\annotations\phpDocReader\PhpDocReader;
use framework\annotations\Resource;

return [
    Resource::class => function (ReflectionProperty $property, $instance, Resource $self) {
        //获取变量注入的类型
        $propertyClass = (new PhpDocReader())->getPropertyClass($property);
        if (class_exists($propertyClass)) {
            $resource = container()->get($propertyClass);
            $property->setAccessible(true);
            $property->setValue($instance, $resource);
        }
    },
];
