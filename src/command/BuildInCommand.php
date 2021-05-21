<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\command;

use framework\command\buildIn\ListCommand;
use framework\command\buildIn\MakeAspectCommand;
use framework\command\buildIn\MakeCommandCommand;
use framework\command\buildIn\MakeControllerCommand;
use framework\command\buildIn\MakeDaoCommand;
use framework\command\buildIn\MakeMiddlewareCommand;
use framework\command\buildIn\MakeServiceCommand;
use framework\database\command\MakeEntityCommand;
use framework\yapi\command\YapiCommand;

/**
 * Class BuildInCommand
 * @package framework\command
 */
class BuildInCommand
{
    private $commands = [
        ListCommand::class,
        MakeEntityCommand::class,
        MakeCommandCommand::class,
        MakeControllerCommand::class,
        MakeServiceCommand::class,
        MakeDaoCommand::class,
        MakeAspectCommand::class,
        MakeMiddlewareCommand::class,
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
