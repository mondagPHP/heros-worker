<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command;

use framework\command\exception\CommandException;

/**
 * make stub脚本 初始工作
 * Trait MakeTraits
 * @package framework\command
 */
trait MakeTraits
{
    protected $rootPath = '';
    protected $filePath = '';
    protected $path = '';
    protected $className = '';
    protected $namespace = '';

    /**
     * 初始化基本make属性
     * @param Input $input
     */
    protected function initMakeProperties(Input $input): void
    {
        $this->initRootPath();
        $this->setPath($input);
        $this->checkPath();
        $this->setNamespace();
        $this->setClassName($input);
    }

    /**
     * 根据stub创建文件
     * @param \Closure $closure 返回code
     */
    protected function newByStub(\Closure $closure): void
    {
        $code = $closure();
        if (! is_string($code)) {
            throw new CommandException('根据stub创建文件newByStub():参数闭包返回值必须是string');
        }
        file_put_contents($this->getFile(), $code);
    }

    /**
     * 初始根目录
     */
    protected function initRootPath(): void
    {
        $this->rootPath = app_path();
    }

    /**
     * 初始文件path目录
     * @param Input $input
     */
    protected function setPath(Input $input): void
    {
        $this->path = trim($input->getOption('path'), '\\');
    }

    /**
     * 设置命名空间
     */
    protected function setNamespace(): void
    {
        $this->namespace = str_replace('/', '\\', $this->path);
    }

    /**
     * 初始文件path目录
     * @param Input $input
     */
    protected function setClassName(Input $input): void
    {
        $this->className = trim($input->getOption('class'));
    }

    /**
     * 是否存在class
     * @return bool
     */
    protected function existClass(): bool
    {
        return class_exists($this->getFullClassName());
    }

    protected function throwExistException(): void
    {
        throw new CommandException(sprintf('%s 类已存在，创建失败', $this->getFullClassName()));
    }

    /**
     * 检查文件path文件夹，不存在则创建
     * @return bool
     */
    protected function checkPath(): bool
    {
        if (strpos($this->path, 'app/') !== 0) {
            throw new CommandException('path 参数错误，必须以“app/”开始');
        }
        $this->filePath = $this->rootPath . substr($this->path, 3);
        if (is_dir($this->filePath)) {
            return true;
        }
        try {
            if (! mkdir($concurrentDirectory = $this->filePath, 0777, true) && ! is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        } catch (\Exception $e) {
            throw  new CommandException('can not create dir: ' . $this->filePath);
        }
        return true;
    }

    protected function getFile(): string
    {
        return $this->filePath . '/' . sprintf('%s.php', $this->className);
    }

    protected function getFullClassName(): string
    {
        return $this->namespace . '\\' . $this->className;
    }

}
