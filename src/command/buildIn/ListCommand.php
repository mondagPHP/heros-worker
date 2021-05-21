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
use framework\command\Manager;

class ListCommand extends AbstractCommand
{
    protected $name = 'list';

    protected $description = '展示所有command';

    public function run(Input $input = null): void
    {
        $commands = Manager::getCommands();
        $lists = [];
        /**
         * @var string $name
         * @var AbstractCommand $command
         */
        foreach ($commands as $name => $command) {
            $lists[] = sprintf("%20s\t%s\n", $name, $command->description());
        }
        echo implode('', $lists);
    }
}

