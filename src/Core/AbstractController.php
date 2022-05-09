<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Core;

/**
 * 增加控制器控制是否登陆
 * 会继承middleware
 */
abstract class AbstractController
{
    public array $middlewares = [];
}
