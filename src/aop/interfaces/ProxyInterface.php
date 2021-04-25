<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\aop\interfaces;

/**
 * Interface ProxyInterface.
 */
interface ProxyInterface
{
    public function process(ProceedingJoinPointInterface $entryClass);
}
