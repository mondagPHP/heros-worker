<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command\interfaces;

interface InputInterface
{
    public function getOption(string $name);

    public function hasOption(string $name): bool;
}
