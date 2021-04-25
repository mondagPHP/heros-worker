<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command;

/**
 * 命令行参数定义器
 * Class OptionDefinition.
 */
class OptionDefinition
{
    /**
     * 参数InputOption集合.
     * @var array
     */
    private $definitions = [];

    public function __construct(array $definitions = [])
    {
        $this->parse($definitions);
    }

    /**
     * get 参数InputOption集合.
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->definitions[$name]);
    }

    public function getInputOption(string $name): ?InputOption
    {
        return $this->definitions[$name] ?? null;
    }

    /**
     * 解析定义options选项.
     */
    private function parse(array $definitions): void
    {
        foreach ($definitions as $item) {
            $option = new InputOption($item);
            if ($option->valid()) {
                $this->definitions[$option->getName()] = $option;
            }
        }
    }
}
