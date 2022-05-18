<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Crontab;

use Monda\Utils\File\FileUtil;

/**
 * 定时任务锁
 */
class CrontabSingleLock
{
    private $fileHandler;

    public function __construct(string $key)
    {
        $lockDir = runtime_path() . '/lock/';
        $bool = FileUtil::makeFileDirs($lockDir);
        if ($bool === false) {
            throw new \RuntimeException("create path ({$lockDir}) error!!!");
        }
        $this->fileHandler = fopen($lockDir . md5($key) . '.lock', 'wb');
    }

    /**
     * 删除文件.
     */
    public function __destruct()
    {
        if ($this->fileHandler !== false) {
            fclose($this->fileHandler);
        }
    }

    /**
     * 尝试去获取锁.
     */
    public function tryLock(): bool
    {
        return ! (flock($this->fileHandler, LOCK_EX | LOCK_NB) === false);
    }

    /**
     * 释放锁
     */
    public function unlock(): bool
    {
        return ! (flock($this->fileHandler, LOCK_UN) === false);
    }
}
