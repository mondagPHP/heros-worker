<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
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
