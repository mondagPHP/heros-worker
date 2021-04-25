<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\aop;

use framework\aop\exception\ConfigException;
use framework\aop\interfaces\ProxyInterface;

class Config
{
    private $config = [];

    private $rebuild = false;

    private $aspectsClasses = [];

    private $path = __DIR__ . '/runtime/aopProxyClasses';

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function parse()
    {
        $this->setRebuild();
        $this->setPath();
        $this->collectAspects();
    }

    public function getAspectsClasses(): array
    {
        return $this->aspectsClasses;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRebuild(): bool
    {
        return $this->rebuild;
    }

    private function collectAspects(): void
    {
        if (isset($this->config['scans']) && is_array($this->config['scans'])) {
            foreach ($this->config['scans'] as $dir => $namespace) {
                if (! is_string($dir) || ! is_string($namespace)) {
                    throw new ConfigException('aop config err: config with key : scans');
                }
                $this->recursiveScan($dir, $namespace);
            }
        }
        if (isset($this->config['aspect']) && is_array($this->config['aspect'])) {
            foreach ($this->config['aspect'] as $aspectClass) {
                $this->mergeAspectClass($aspectClass);
            }
        }
    }

    /**
     * 递归扫描目录下的aspect.
     * @param $dir
     * @param $namespace
     */
    private function recursiveScan($dir, $namespace): void
    {
        if (! file_exists($dir)) {
            throw new ConfigException('dir not exists');
        }
        $files = scandir($dir);
        foreach ($files as $file) {
            $fullPath = $dir . '/' . $file;
            if ('.' === $file || '..' === $file) {
                continue;
            }
            if (is_file($fullPath) && ('php' === pathinfo($file, PATHINFO_EXTENSION))) {
                $this->mergeAspectClass($namespace . '\\' . pathinfo($file, PATHINFO_FILENAME));
            }
            if (is_dir($fullPath)) {
                $this->recursiveScan($fullPath, $namespace . '\\' . basename($file));
            }
        }
    }

    /**
     * @param $class
     */
    private function mergeAspectClass(string $class): void
    {
        if (class_exists($class) && new $class() instanceof ProxyInterface) {
            $this->aspectsClasses = array_merge($this->aspectsClasses, [$class]);
        }
    }

    private function setPath(): void
    {
        $path = $this->getValue('path') ?: $this->path;
        if (! empty($path)) {
            try {
                if (! file_exists($path) && ! mkdir($path, 0766, true) && ! is_dir($path)) {
                    throw new ConfigException('can not create aop proxy directory: ' . $path);
                }
                $this->path = $path;
            } catch (\Exception $exception) {
                throw new ConfigException('can not create aop proxy directory: ' . $path);
            }
        }
    }

    private function setRebuild(): void
    {
        if (true === $this->getValue('rebuild')) {
            $this->rebuild = true;
        }
    }

    /**
     * @param $key
     * @return null|mixed
     */
    private function getValue($key)
    {
        return $this->config[$key] ?? null;
    }
}
