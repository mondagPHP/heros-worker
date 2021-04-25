<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\boot;

use framework\annotations\Component;

/**
 * @Component()
 * Class MiddleWareCollector
 */
class MiddleWareCollector
{
    protected $middlewares = [];

    /**
     * MiddleWareCollector constructor.
     */
    public function __construct()
    {
        $config = config('middleware', []);
        ksort($config);
        $this->middlewares = $config;
    }

    public function get(string $path): array
    {
        $middlewares = $this->middlewares['global'] ?? [];
        foreach ($this->middlewares ?? [] as $uri => $config) {
            $uri = current(explode('*', $uri));
            if (false !== strpos($path, $uri)) {
                $middlewares = array_merge($middlewares, $config);
            }
        }
        return $middlewares;
    }
}
