<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Casbin;

use Carbon\Carbon;
use Framework\Database\HeroModel;

/**
 * Class CasbinRule
 *
 * @property int $id
 * @property string $ptype
 * @property string $v0
 * @property string $v1
 * @property string $v2
 * @property string $v3
 * @property string $v4
 * @property string $v5
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class CasbinRule extends HeroModel
{
    protected $table = 'casbin_rule';

    /** @var array */
    protected $fillable = ['id', 'ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];
}
