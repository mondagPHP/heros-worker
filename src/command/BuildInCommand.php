<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command;

use framework\database\command\MakeEntityCommand;
use framework\yapi\command\YapiCommand;

/**
 * Class BuildInCommand
 * @package framework\command
 */
class BuildInCommand
{
    private $commands = [
        MakeEntityCommand::class,
        YapiCommand::class
    ];

    /** @var Manager $manager */
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function build(): void
    {
        foreach ($this->commands as $command) {
            $this->manager->register($command);
        }
    }
}
