<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command;

use framework\command\interfaces\InputInterface;

/**
 * 命令行输入参数
 * Class Input.
 */
class Input implements InputInterface
{
    /** @var array */
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param $value
     * @return Input
     */
    public function withAddOption(string $name, $value): self
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * @return null|mixed
     */
    public function getOption(string $name)
    {
        if ($this->hasOption($name)) {
            return $this->options[$name];
        }
        return null;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
