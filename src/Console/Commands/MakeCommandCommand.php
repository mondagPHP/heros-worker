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

class MakeCommandCommand extends Command
{
    protected static $defaultName = 'make:command';

    protected static $defaultDescription = '生成命令行工具类';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Command name');
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $name = $input->getArgument('name');
        $output->writeln("Make command $name");
        if (! ($pos = strrpos($name, '/'))) {
            $name = $this->getClassName($name);
            $file = "app/Command/$name.php";
            $namespace = 'App\Command';
        } else {
            $path = 'app/'.substr($name, 0, $pos).'/Command';
            $name = $this->getClassName(substr($name, $pos + 1));
            $file = "$path/$name.php";
            $namespace = str_replace('/', '\\', $path);
        }
        $this->createCommand($name, $namespace, $file, $command);

        return self::SUCCESS;
    }

    protected function getClassName($name): string
    {
        return preg_replace_callback('/:([a-zA-Z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, ucfirst($name)).'Command';
    }

    /**
     * @param $name
     * @param $namespace
     * @param $file
     * @param $command
     * @return void
     */
    protected function createCommand($name, $namespace, $file, $command)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $desc = str_replace(':', ' ', $command);
        $commandContent = <<<EOF
<?php
declare(strict_types=1);
namespace $namespace;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class $name extends Command
{
    protected static \$defaultName = '$command';
    protected static \$defaultDescription = '$desc';

    /**
     * @return void
     */
    protected function configure(): void
    {
        \$this->addArgument('name', InputArgument::OPTIONAL, 'Name description');
    }

    /**
     * @param InputInterface \$input
     * @param OutputInterface \$output
     * @return int
     */
    protected function execute(InputInterface \$input, OutputInterface \$output): int
    {
        \$name = \$input->getArgument('name');
        \$output->writeln('Hello $command');
        return self::SUCCESS;
    }

}
EOF;
        file_put_contents($file, $commandContent);
    }
}
