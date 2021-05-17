<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\yapi;

class Yapi
{
    private $config;
    private $scanPath = [];
    private $controllerMap;
    private $methodMap;

    public static function run(string $module): void
    {
        $yapi = new self($module);
        $yapi->collectMap();
        $yapi->createJson();
    }

    private function __construct(string $module)
    {
        $this->config = \config('yapi');
        $this->setScanPath($module);
    }

    /**
     * @throws \ReflectionException
     */
    protected function collectMap(): void
    {
        $collector = new Collector($this->scanPath);
        [$this->controllerMap, $this->methodMap] = $collector->collector();
    }

    protected function createJson(): void
    {
        $jsonCreate = new Json($this->config['json_path']);
        $jsonCreate->export($this->controllerMap, $this->methodMap);
    }

    /**
     * @param string $module
     */
    protected function setScanPath(string $module): void
    {
        if (! isset($this->config['scan_path'][$module]) && strtoupper($module) !== 'ALL') {
            $this->scanPath = [];
            return;
        }
        if (strtoupper($module) === 'ALL') {
            foreach ($this->config['scan_path'] ?? [] as $path) {
                $this->scanPath = array_merge($this->scanPath, $path);
            }
            return;
        }
        $this->scanPath = $this->config['scan_path'][$module];
    }
}
