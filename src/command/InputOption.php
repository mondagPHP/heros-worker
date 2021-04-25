<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command;

class InputOption
{
    private $name;

    private $require = false;

    private $description = '';

    public function __construct(array $option)
    {
        $this->parse($option);
    }

    /**
     * 选项是否有效.
     */
    public function valid(): bool
    {
        return ! empty($this->name);
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * 选项是否必填，不能为空.
     */
    public function isRequire(): bool
    {
        return $this->require;
    }

    /**
     * parse option.
     */
    private function parse(array $option): void
    {
        $nums = count($option);
        if (0 === $nums) {
            return;
        }
        $this->name = Parser::parseName($option[0]);
        if (2 === $nums) {
            if ('require' === $option[1]) {
                $this->require = true;
            } else {
                $this->description = $option[1];
            }
            return;
        }
        if ($nums >= 3) {
            if ('require' === $option[1]) {
                $this->require = true;
            }
            $this->description = $option[2];
            return;
        }
    }
}
