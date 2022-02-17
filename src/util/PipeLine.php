<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace Framework\Util;

use Closure;

/**
 * 管道操作
 */
class PipeLine
{
    //所有要执行的类
    protected array $classes = [];

    //类的方法名称
    protected string $handleMethod = 'process';

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
            return function ($request, ...$cusArgs) use ($res, $currClass) {
                return (new $currClass())->{$this->handleMethod}($request, $res, ...$cusArgs);
            };
        }, $initial);
    }
}
