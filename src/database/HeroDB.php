<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\database;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Query\Builder;

/**
 * Class HeroDB.
 * @method Builder table(string $table, null|string $connection = null)
 */
class HeroDB extends Manager
{
}
