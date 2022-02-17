<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace Framework\Component;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Framework\Annotation\Component;
use function FastRoute\simpleDispatcher;

#[Component]
class RouterCollector
{
    public array $routers = [];

    public function addRouter(string $method, string $uri, callable $handler): void
    {
        $this->routers[] = ['method' => $method, 'uri' => $uri, 'handler' => $handler,];
    }

    /**
     * 获取路由调度器.
     */
    public function getDispatcher(): Dispatcher
    {
        return simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routers ?? [] as $router) {
                $r->addRoute($router['method'], $router['uri'], $router['handler']);
            }
        });
    }
}
