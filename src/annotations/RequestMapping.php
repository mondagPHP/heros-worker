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
 * @Target({"METHOD", "CLASS"})
 * Class Controller
 */
class RequestMapping
{
    public $value = '';
    public $method = [];
    public $msg = '';
}
