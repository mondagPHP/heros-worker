<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\yapi\command;

use framework\command\AbstractCommand;
use framework\command\Input;
use framework\yapi\Yapi;

class YapiCommand extends AbstractCommand
{
    //command 名称
    protected $name = 'yapi';

    //command 描述
    protected $description = 'yapi json导入生成工具';

    //command 定义
    protected $definition;

    /**
     * eg:  php artisan.php test
     * output: test.
     * @param Input|null $input
     */
    public function run(Input $input = null): void
    {
        echo '正在生成Yapi json文件...' . PHP_EOL;
        $module = $input->getOption('module');
        Yapi::run($module);
    }

    /**
     * 命令行参数
     * [[参数名称， 是否必填， 描述], ...].
     */
    public function optionDefinition(): array
    {
        return [
            ['-module', 'require', 'module name'],
        ];
    }
}
