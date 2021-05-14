<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\core;

use framework\util\Arr;

/**
 * Class Controller.
 */
abstract class AbstractController
{
    protected $middlewares = [];
    /**
     * 注册initialize方法
     * Controller constructor.
     */
    public function __construct()
    {
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }

    /**
     * @param string|array $middlewares
     * @param array $except
     * @param array $only
     * @return $this
     * date 2021/5/12
     */
    public function middleware($middlewares, array $except = [], array $only = []): self
    {
        $filter = function (array $arr) {
            $resArr = [];
            foreach ($arr ?? [] as $items) {
                if (is_string($items)) {
                    $resArr[] = $items;
                }
                if (is_array($items)) {
                    foreach ($items as $secondItem) {
                        if (is_string($secondItem)) {
                            $resArr[] = $items;
                        }
                    }
                }
            }
            return $resArr;
        };
        $localExcept = $filter(Arr::pack($except['except'] ?? []));
        $localOnly = $filter(Arr::pack($only['only'] ?? []));
        $middlewares = Arr::pack($middlewares);
        foreach ($middlewares as $middleware) {
            if (isset($this->middlewares[$middleware])) {
                $this->middlewares[$middleware]['except'] = array_unique(array_merge($this->middlewares[$middleware]['except'], $localExcept));
                $this->middlewares[$middleware]['only'] = array_unique(array_merge($this->middlewares[$middleware]['only'], $localOnly));
            } else {
                $this->middlewares[$middleware] = [
                    'except' => $localExcept,
                    'only' => $localOnly,
                ];
            }
        }
        return $this;
    }

    public function getMiddleware(string $method = '', $global = []): array
    {
        if (! $method) {
            return $global;
        }
        $sortMiddles = [];
        foreach ($global as $item) {
            $sortMiddles[$item] = [
                'except' => [],
                'only' => [],
            ];
        }
        foreach ($this->middlewares as $middleware => $conditions) {
            if (isset($sortMiddles[$middleware])) {
                $sortMiddles[$middleware]['except'] = array_merge($sortMiddles[$middleware]['except'], $conditions['except']);
                $sortMiddles[$middleware]['only'] = array_merge($sortMiddles[$middleware]['only'], $conditions['only']);
            } else {
                $sortMiddles[$middleware] = $conditions;
            }
        }
        unset($middleware);
        $return = [];
        foreach ($sortMiddles as $sortMiddle => $conditions) {
            if (in_array($method, $conditions['except'], true)) {
                continue;
            }
            if (! empty($conditions['only']) && ! in_array($method, $conditions['only'], true)) {
                continue;
            }
            $return[] = $sortMiddle;
        }
        return $return;
    }
}
