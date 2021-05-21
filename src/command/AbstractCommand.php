<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command;

use framework\command\exception\CommandException;
use framework\command\interfaces\CommandInterface;

abstract class AbstractCommand implements CommandInterface
{
    /** @var string command 名称 */
    protected $name = '';

    /** @var string command 描述 */
    protected $description = '';

    /** @var OptionDefinition 命令行参数定义器 */
    protected $definition;

    public function __construct()
    {
        $this->init();
    }

    public function __toString(): string
    {
        $str = 'usage: php artisan ' . $this->name . ' [-option=value] [...] ' . PHP_EOL;
        $str .= 'description: ' . $this->description . PHP_EOL;
        $str .= sprintf('%-15s%-10s  %s' . PHP_EOL, 'option', 'isRequire', 'description');
        /** @var InputOption $inputOption */
        foreach ($this->definition->getDefinitions() as $inputOption) {
            $str .= sprintf('%-15s%-10s  %s' . PHP_EOL, $inputOption->getName(), $inputOption->isRequire() ? 'true' : 'false', $inputOption->getDescription());
        }
        return $str;
    }

    public function init(): void
    {
        $this->definition = new OptionDefinition($this->optionDefinition());
    }

    public function getDefinition(): OptionDefinition
    {
        return $this->definition;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function run(Input $input = null): void
    {
        throw new CommandException('command : the run method is not implemented');
    }

    public function optionDefinition(): array
    {
        return [];
    }
}
