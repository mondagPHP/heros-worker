<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Queue;

use Framework\Core\Log;
use Workerman\Worker;

/**
 * Class Consumer.
 */
class Consumer
{
    /**
     * @var string
     */
    protected string $consumerDir;

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
    public function onWorkerStart(Worker $worker)
    {
        if (file_exists($this->consumerDir)) {
            $dirIterator = new \RecursiveDirectoryIterator($this->consumerDir);
            $iterator = new \RecursiveIteratorIterator($dirIterator);
            /**  @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    continue;
                }
                $ext = $file->getExtension();
                if ('php' === $ext) {
                    $class = '\\' . ucfirst(str_replace('/', '\\', substr(substr($file->getPath(), strlen(BASE_PATH)), 1))) . '\\' . substr($file->getFilename(), 0, -4);
                    if (! class_exists($class)) {
                        Log::error("{$class} not exist!");
                        continue;
                    }
                    $consumer = container($class);
                    $connectionName = $consumer->connection ?? 'default';
                    $queue = $consumer->queue;
                    $connection = Client::connection($connectionName);
                    $connection->subscribe($queue, [$consumer, 'consume']);
                }
            }
        }
    }
}
