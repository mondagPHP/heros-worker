<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\core;

/**
 * Class Controller.
 */
abstract class Controller
{
    /**
     * 注册initialize方法
     * Controller constructor.
     */
    public function __construct()
    {
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }

    public function getMiddleware(): array
    {
        return [];
    }
}
