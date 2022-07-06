<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Component;

use Framework\Annotation\Component;
use Framework\Middleware\PageMiddleware;

#[Component]
class MiddleWareCollector
{
    /**
     * MiddleWareCollector constructor.
     */
    public function __construct(private array $middlewares = [])
    {
        $config = config('middleware', []);
        ksort($config);
        $this->middlewares = (array) $config;
    }

    /**
     * @param  string  $path
     * @return array
     */
    public function get(string $path): array
    {
        $middlewares = $this->middlewares['global'] ?? [];
        $middlewares[] = PageMiddleware::class;
        foreach ($this->middlewares ?? [] as $uri => $config) {
            $uri = current(explode('*', $uri));
            if (str_contains($path, $uri)) {
                $middlewares = array_merge($middlewares, $config);
            }
        }

        return $middlewares;
    }
}
