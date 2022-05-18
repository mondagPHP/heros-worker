<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Console\Commands;

use Framework\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends Command
{
    protected static $defaultName = 'version';

    protected static $defaultDescription = 'Show heros-worker version';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $installedFile = base_path() . '/vendor/composer/installed.php';
        if (is_file($installedFile)) {
            $versionInfo = include $installedFile;
        }
        $herosworkerVersion = Application::VERSION;
        $output->writeln("heros-worker version $herosworkerVersion");
        $herosworkerVersion = $versionInfo['versions']['mondagroup/heros-worker']['pretty_version'] ?? '';
        $output->writeln("heros-worker install $herosworkerVersion");
        return self::SUCCESS;
    }
}
