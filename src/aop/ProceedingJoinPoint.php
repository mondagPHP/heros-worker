<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\aop;

use framework\aop\exception\ProceedingJoinPointException;
use framework\aop\interfaces\ProceedingJoinPointInterface;

/**
 * Class EntryClass.
 */
class ProceedingJoinPoint implements ProceedingJoinPointInterface
{
    public $arguments;

    /** @var \Closure */
    public $pipe;

    protected $className;

    protected $classMethod;

    /** @var \Closure */
    protected $originClosure;

    public function __construct($className, $classMethod, $arguments, $originClosure)
    {
        $this->className = $className;
        $this->classMethod = $classMethod;
        $this->arguments = $arguments;
        $this->originClosure = $originClosure;
    }

    public function getClassMethod()
    {
        return $this->classMethod;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function process()
    {
        $c = $this->pipe;
        if (! $this->pipe instanceof \Closure) {
            throw new ProceedingJoinPointException('entry class pipe must be closure');
        }
        return $c($this);
    }

    public function processOriginClosure()
    {
        $this->pipe = null;
        $c = $this->originClosure;
        return $c(...$this->arguments);
    }
}
