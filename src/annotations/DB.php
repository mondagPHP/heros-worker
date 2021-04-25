<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * Class Controller
 */
class DB
{
    public $connection = 'default';
}
