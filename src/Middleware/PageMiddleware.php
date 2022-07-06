<?php
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Middleware;

use Framework\Contract\MiddlewareInterface;
use Framework\Http\HttpRequest;
use Illuminate\Pagination\Paginator;

/**
 * 分页中间件
 */
class PageMiddleware implements MiddlewareInterface
{
    public function process(HttpRequest $request, callable $handler): mixed
    {
        $pageParameterConfig = config('request.pageParameter', 'page');
        $page = (int) $request->getParameter($pageParameterConfig, 1);
        if ($page <= 0) {
            $page = 1;
        }
        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        return $handler($request);
    }
}
