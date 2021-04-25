<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
use framework\annotations\Component;

return [
    Component::class => function ($instance, Component $self) {
        $vars = get_object_vars($self);
        if (isset($vars['name']) && '' !== $vars['name']) {
            $beanName = $vars['name'];
            container()->set($beanName, $instance);
        }
    },
];
