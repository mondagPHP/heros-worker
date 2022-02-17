<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
use Framework\Annotation\DB;
use Framework\Database\HerosDB;

return [
    DB::class => function (ReflectionProperty $property, mixed $instance, \ReflectionAttribute $self) {
        $connection = $self->newInstance()?->connection ?: 'default';
        $property->setAccessible(true);
        $property->setValue($instance, HerosDB::connection($connection));
    },
];
