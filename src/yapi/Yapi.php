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
    private $controllerMap;
    private $methodMap;

    public static function run(): void
    {
        $yapi = new self();
        $yapi->collectMap();
        $yapi->createJson();
    }

    private function __construct()
    {
        $this->config = \config('yapi');
    }

    /**
     * @throws \ReflectionException
     */
    protected function collectMap(): void
    {
        $collector = new Collector($this->config['scan_path']);
        [$this->controllerMap, $this->methodMap] = $collector->collector();
    }

    protected function createJson(): void
    {
        $jsonCreate = new Json($this->config['json_path']);
        $jsonCreate->export($this->controllerMap, $this->methodMap);
    }
}
