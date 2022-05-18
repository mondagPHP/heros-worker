<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Process;

use Workerman\Timer;
use Workerman\Worker;

/**
 * 监听器
 */
class Monitor
{
    /**
     * @var array
     */
    protected array $_paths = [];

    /**
     * @var array
     */
    protected array $_extensions = [];

    /**
     * FileMonitor constructor.
     * @param array $monitorDir
     * @param array $monitorExtensions
     */
    public function __construct(array $monitorDir, array $monitorExtensions)
    {
        $this->_paths = $monitorDir;
        $this->_extensions = $monitorExtensions;
        if (! Worker::getAllWorkers()) {
            return;
        }
        $disableFunctions = explode(',', ini_get('disable_functions'));
        if (in_array('exec', $disableFunctions, true)) {
            echo "\nMonitor file change turned off because exec() has been disabled by disable_functions setting in " . PHP_CONFIG_FILE_PATH . "/php.ini\n";
        } else {
            if (! Worker::$daemonize) {
                Timer::add(1, function () {
                    $this->checkAllFilesChange();
                });
            }
        }
    }

    /**
     * @return void
     */
    public function checkAllFilesChange(): void
    {
        foreach ($this->_paths as $path) {
            $this->checkFilesChange($path);
        }
    }

    /**
     * @param string $monitorDir
     * @return void
     */
    private function checkFilesChange(string $monitorDir):void
    {
        static $lastMtime;
        if (! $lastMtime) {
            $lastMtime = time();
        }
        clearstatcache();
        if (! is_dir($monitorDir)) {
            if (! is_file($monitorDir)) {
                return;
            }
            $iterator = [new \SplFileInfo($monitorDir)];
        } else {
            // recursive traversal directory
            $dirIterator = new \RecursiveDirectoryIterator($monitorDir, \FilesystemIterator::FOLLOW_SYMLINKS);
            $iterator = new \RecursiveIteratorIterator($dirIterator);
        }
        foreach ($iterator as $file) {
            /** var SplFileInfo $file */
            if ($file->isDir()) {
                continue;
            }
            // check mtime
            if ($lastMtime < $file->getMTime() && in_array($file->getExtension(), $this->_extensions, true)) {
                $var = 0;
                exec(PHP_BINARY . ' -l ' . $file, $out, $var);
                $lastMtime = $file->getMTime();
                if ($var) {
                    continue;
                }
                echo $file . " update and reload\n";
                if (DIRECTORY_SEPARATOR === '/') {
                    posix_kill(posix_getppid(), SIGUSR1);
                }
                break;
            }
        }
    }
}
