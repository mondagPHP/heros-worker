<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\annotations;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * Class Controller
 */
class Config
{
    public $name = '';

    public $default;
}
