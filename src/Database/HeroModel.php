<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */

namespace Framework\Database;

use Framework\Database\Filter\FilterableTrait;
use Illuminate\Database\Eloquent\Model as BaseModel;

class HeroModel extends BaseModel
{
    use FilterableTrait;
}
