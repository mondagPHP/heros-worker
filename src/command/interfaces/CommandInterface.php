<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command\interfaces;

use framework\command\Input;
use framework\command\OptionDefinition;

interface CommandInterface
{
    public function __toString(): string;

    public function run(Input $input = null);

    public function name(): string;

    public function optionDefinition(): array;

    public function getDefinition(): OptionDefinition;
}
