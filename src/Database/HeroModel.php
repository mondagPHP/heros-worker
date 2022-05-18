<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Database;

use Framework\Database\Filter\FilterableTrait;
use Illuminate\Database\Eloquent\Model as BaseModel;

class HeroModel extends BaseModel
{
    use FilterableTrait;
}
