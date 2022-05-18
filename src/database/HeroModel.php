<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */

namespace framework\database;

use framework\database\filters\FilterableTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HeroModel.
 */
abstract class HeroModel extends Model
{
    use FilterableTrait;
}
