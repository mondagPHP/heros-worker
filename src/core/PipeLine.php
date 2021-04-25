<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\core;

use Closure;

/**
 * Class PipeLine.
 */
class PipeLine
{
    //所有要执行的类
    protected $classes = [];

    //类的方法名称
    protected $handleMethod = 'handle';

    //需要创建新对象
    public function create(): self
    {
        return clone $this;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setHandleMethod($method): self
    {
        $this->handleMethod = $method;
        return $this;
    }

    /**
     * @param $classes
     * @return $this
     */
    public function setClasses($classes): self
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * 管道操作.
     */
    public function run(Closure $initial): Closure
    {
        return array_reduce(array_reverse($this->classes), function ($res, $currClass) {
            return function ($request, $vars, $extVars, ...$cusArgs) use ($res, $currClass) {
                return (new $currClass())->{$this->handleMethod}($request, $vars, $extVars, $res, ...$cusArgs);
            };
        }, $initial);
    }
}
