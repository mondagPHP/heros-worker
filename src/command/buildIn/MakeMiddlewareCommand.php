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
 * Class MakeControllerCommand
 * @package framework\command\buildIn
 */
class MakeMiddlewareCommand extends AbstractCommand
{
    use MakeTraits;

    protected $name = 'make:middleware';

    protected $description = '快速创建middleware文件';

    private $commandName = '';

    private $commandDesc = '';

    public function run(Input $input = null): void
    {
        $this->initMakeProperties($input);
        if ($this->existClass()) {
            $this->throwExistException();
        }
        $this->newByStub(function () {
            $stubFile = __DIR__ . '/stubs/middleware.stub';
            $code = file_get_contents($stubFile);
            return str_replace(
                ['{{namespace}}', '{{className}}'],
                [$this->namespace, $this->className],
                $code
            );
        });
        echo sprintf("%s 创建成功\n", $this->getFullClassName());
    }

    public function optionDefinition(): array
    {
        return [
            ['-path', 'require', '以app_path()根目录开始,命名空间与路径对应 eg:app/middleware'],
            ['-class', 'require', 'middleware类名'],
        ];
    }
}
