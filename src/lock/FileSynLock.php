<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\lock;

use framework\file\FileUtils;

/**
 * Class FileSynLock.
 */
class FileSynLock implements ISynLock
{
    private $fileHandler;  //文件资源柄

    public function __construct($key)
    {
        $lockDir = runtime_path() . '/lock/';
        FileUtils::makeFileDirs($lockDir);
        $this->fileHandler = fopen($lockDir . md5($key) . '.lock', 'wb');
    }

    /**
     * 去除.
     */
    public function __destruct()
    {
        if (false !== $this->fileHandler) {
            fclose($this->fileHandler);
        }
    }

    /**
     * 尝试去获取锁，成功返回false并且一直阻塞.
     */
    public function tryLock(): bool
    {
        return ! (false === flock($this->fileHandler, LOCK_EX));
    }

    /**
     * 释放锁
     */
    public function unlock(): bool
    {
        return ! (false === flock($this->fileHandler, LOCK_UN));
    }
}
