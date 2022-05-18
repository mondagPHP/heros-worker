<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as Commands;

class Command extends Application
{
    public function installInternalCommands()
    {
        $this->installCommands(__DIR__ . '/Commands', 'Framework\Console\Commands');
    }

    public function installCommands($path, $namespace = 'App\Command')
    {
        $dirIterator = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($dirIterator);
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() == '..') {
                continue;
            }
            $className = $namespace . '\\' . basename($file->getFilename(), '.php');
            if (! is_a($className, Commands::class, true)) {
                continue;
            }
            $this->add(new $className);
        }
    }
}
