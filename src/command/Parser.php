<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command;

/**
 * 参数解析器
 * Class Parser.
 */
class Parser
{
    /**
     * 命令参数解析.
     * @param $argv
     */
    public static function parse($argv): array
    {
        $argc = count($argv);
        if ($argc <= 1) {
            self::help();
        }
        if (! Manager::hasCommand($argv[1])) {
            echo 'no such command' . PHP_EOL;
            exit;
        }
        $command = Manager::getCommand($argv[1]);
        $definition = $command->getDefinition();
        $input = new Input([]);
        unset($argv[0], $argv[1]);
        foreach ($argv as $item) {
            $params = explode('=', $item);
            $option = self::parseName($params[0] ?? '');
            $value = $params[1] ?? '';
            if (! empty($option)) {
                $input->withAddOption($option, $value);
            }
        }
        /**
         * @var $name
         * @var InputOption $inputOption
         */
        foreach ($definition->getDefinitions() as $name => $inputOption) {
            if ($inputOption->isRequire() && ! $input->hasOption($name)) {
                echo $command;
                exit;
            }
        }

        foreach ($input->getOptions() as $name => $value) {
            if (! $definition->hasOption($name)) {
                echo $command;
                exit;
            }
            if (empty(trim($value)) && $definition->getInputOption($name)->isRequire()) {
                echo sprintf('command:[%s] option [%s] require value, empty get', $command->name(), $definition->getInputOption($name)->getName()) . PHP_EOL;
                exit;
            }
        }
        return [$command, $input];
    }

    /**
     * 解析option名称.
     * @param $name
     * @return false|string
     */
    public static function parseName($name): string
    {
        if (0 !== strpos($name, '-')) {
            return '';
        }
        if (0 === strpos($name, '--')) {
            return substr($name, 2);
        }
        return substr($name, 1);
    }

    /**
     * output help.
     */
    public static function help(): void
    {
        echo 'usage: php artisan commandName [--option=value] [...]' . PHP_EOL;
        exit;
    }
}
