<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command;

use framework\command\exception\CommandConfigException;
use framework\command\exception\CommandException;
use framework\command\interfaces\CommandInterface;

/**
 * command管理器
 * Class Manager.
 */
class Manager
{
    private static $commands = [];

    /**
     * 执行command命令.
     * @param  CommandInterface|string $command
     * @param  null                    $input
     * @return mixed
     */
    public static function exec($command, $input = null)
    {
        if ($command instanceof CommandInterface) {
            return $command->run($input);
        }
        if (! self::hasCommand($command)) {
            throw new CommandException('no such command :' . $command);
        }
        $c = self::$commands[$command];
        return $c->run($input);
    }

    /**
     * @param $command
     */
    public static function hasCommand($command): bool
    {
        return isset(self::$commands[$command]);
    }

    /**
     * 扫描command配置，加载所有commands.
     */
    public function scans(array $scanCommands): void
    {
        foreach ($scanCommands as $key => $value) {
            if (is_object($value) || class_exists($value)) {
                $this->register($value);
                continue;
            }
            if (is_dir($key)) {
                if (! is_string($key)) {
                    throw new CommandConfigException(sprintf('command config file error : key : "%s" value must be string', $key));
                }
                $this->recursiveScan($key, $value);
                continue;
            }
            throw new CommandConfigException(sprintf('command config file error : key : "%s"', $key));
        }
    }

    /**
     * 注册command.
     * @param $class
     */
    public function register($class): void
    {
        $instance = null;
        if (is_string($class) && class_exists($class)) {
            $instance = new $class();
        }
        if (is_object($class)) {
            $instance = $class;
        }
        if ($instance instanceof CommandInterface) {
            $this->withAddCommand($instance);
        }
    }

    public static function getCommand(string $name): ?CommandInterface
    {
        return self::$commands[$name] ?? null;
    }

    /**
     * @return $this
     */
    public function withAddCommand(CommandInterface $command): self
    {
        self::$commands[$command->name()] = $command;
        return $this;
    }

    /**
     * 递归扫描目录下的commands.
     * @param $dir
     * @param $namespace
     */
    private function recursiveScan($dir, $namespace): void
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            $fullPath = $dir . '/' . $file;
            if ('.' === $file || '..' === $file) {
                continue;
            }

            if (is_file($fullPath) && ('php' === pathinfo($file, PATHINFO_EXTENSION))) {
                $this->register($namespace . '\\' . pathinfo($file, PATHINFO_FILENAME));
            }
            if (is_dir($fullPath)) {
                $this->recursiveScan($fullPath, $namespace . '\\' . basename($file));
            }
        }
    }
}
