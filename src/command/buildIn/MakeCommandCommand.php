<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command\buildIn;

use framework\command\AbstractCommand;
use framework\command\Input;
use framework\command\MakeTraits;

/**
 * Class MakeCommandCommand
 * @package framework\command\buildIn
 */
class MakeCommandCommand extends AbstractCommand
{
    use MakeTraits;

    protected $name = 'make:command';

    protected $description = '快速创建command文件';

    private $commandName = '';

    private $commandDesc = '';

    public function run(Input $input = null): void
    {
        $this->initMakeProperties($input);
        $this->commandName = $input->getOption('name');
        $this->commandDesc = $input->getOption('desc') ?? '';
        if ($this->existClass()) {
            $this->throwExistException();
        }
        $this->newByStub(function () {
            $stubFile = __DIR__ . '/stubs/command.stub';
            $code = file_get_contents($stubFile);
            return str_replace(
                ['{{namespace}}', '{{className}}', '{{name}}', '{{desc}}'],
                [$this->namespace, $this->className, $this->commandName, $this->commandDesc],
                $code
            );
        });
        echo sprintf("%s 创建成功\n", $this->getFullClassName());
    }

    public function optionDefinition(): array
    {
        return [
            ['-path', 'require', '以app_path()根目录开始,命名空间与路径对应 eg:app/command'],
            ['-class', 'require', 'command类名'],
            ['-name', 'require', 'command->name名称'],
            ['-desc', 'command描述'],
        ];
    }
}
