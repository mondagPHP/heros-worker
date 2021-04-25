<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\boot;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use framework\annotations\Component;
use function FastRoute\simpleDispatcher;

/**
 * @Component()
 * Class RouterCollector
 */
class RouterCollector
{
    private $routers = [];

    public function addRouter($method, $uri, $handler): void
    {
        $this->routers[] = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler,
        ];
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
