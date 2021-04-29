<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\queue\redis\proccess;

use framework\bootstrap\Log;
use framework\queue\redis\Client;

/**
 * Class Consumer.
 */
class Consumer
{
    /**
     * @var string
     */
    protected $consumerDir = '';

    /**
     * StompConsumer constructor.
     */
    public function __construct(string $consumerDir)
    {
        $this->consumerDir = $consumerDir;
    }

    /**
     * onWorkerStart.
     */
    public function onWorkerStart()
    {
        $dirIterator = new \RecursiveDirectoryIterator($this->consumerDir);
        $iterator = new \RecursiveIteratorIterator($dirIterator);
        foreach ($iterator as $file) {
            if (is_dir($file)) {
                continue;
            }
            $fileInfo = new \SplFileInfo($file);
            $ext = $fileInfo->getExtension();
            if ('php' === $ext) {
                $class = str_replace('/', '\\', substr(substr($file, strlen(BASE_PATH)), 0, -4));
                if (! class_exists($class)) {
                    Log::error("{$class} not exist!");
                    continue;
                }
                $consumer = container()->get($class);
                $connectionName = $consumer->connection ?? 'default';
                $queue = $consumer->queue;
                $connection = Client::connection($connectionName);
                $connection->subscribe($queue, [$consumer, 'consume']);
            }
        }
    }
}
