<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Component;

use Framework\Annotation\Component;

#[Component]
class MiddleWareCollector
{
    private array $middlewares = [];

    /**
     * MiddleWareCollector constructor.
     */
    public function __construct()
    {
        $config = config('middleware', []);
        ksort($config);
        $this->middlewares = $config;
    }

    /**
     * @param string $path
     * @return array
     */
    public function get(string $path): array
    {
        $middlewares = $this->middlewares['global'] ?? [];
        foreach ($this->middlewares ?? [] as $uri => $config) {
            $uri = current(explode('*', $uri));
            if (str_contains($path, $uri)) {
                $middlewares = array_merge($middlewares, $config);
            }
        }
        return $middlewares;
    }
}
