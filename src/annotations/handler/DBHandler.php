<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
use framework\annotations\DB;
use framework\database\HeroDB;

return [
    DB::class => function ($property, $instance, DB $self) {
        $connectionName = 'default';
        if ('' !== $self->connection) {
            $connectionName = $self->connection;
        }
        $property->setAccessible(true);
        $property->setValue($instance, HeroDB::connection($connectionName));
        return $instance;
    },
];
