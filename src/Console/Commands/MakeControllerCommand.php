<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeControllerCommand extends Command
{
    protected static $defaultName = 'make:controller';

    protected static $defaultDescription = 'Make controller';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('module', InputArgument::REQUIRED, 'Controller module');
        $this->addArgument('name', InputArgument::REQUIRED, 'Controller name');
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $module = $input->getArgument('module');
        $output->writeln("Make controller $name in {$module}");
        $name = ucfirst($name);
        $module = ucwords($module);
        $file = "app/Modules/{$module}/Action/{$name}Action.php";
        $namespace = "App\\Modules\\{$module}\\Action";
        $this->createController($name, $namespace, $file);

        return self::SUCCESS;
    }

    /**
     * @param $name
     * @param $namespace
     * @param $path
     * @return void
     */
    protected function createController($name, $namespace, $file)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $controllerContent = <<<EOF
<?php
declare(strict_types=1);

namespace $namespace;

use Framework\Annotation\Controller;

#[Controller(name: "name")]
class {$name}Action
{

}
EOF;
        file_put_contents($file, $controllerContent);
    }
}
