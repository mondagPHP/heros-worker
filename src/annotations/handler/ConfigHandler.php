<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
use framework\annotations\Config;

return [
    Config::class => function ($property, $instance, Config $self) {
        $value = \config($self->name, $self->default);
        $property->setAccessible(true);
        $property->setValue($instance, $value);
        return $instance;
    },
];
