<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\aop\interfaces;

/**
 * Interface EntryClassInterface.
 */
interface ProceedingJoinPointInterface
{
    public function process();

    public function getClassMethod();

    public function getClassName();

    public function getArguments();
}
