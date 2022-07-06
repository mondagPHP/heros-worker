<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Contract\MiddlewareInterface;
use Framework\Http\HttpRequest;

class CorMiddleware implements MiddlewareInterface
{
    public function process(HttpRequest $request, callable $handler): mixed
    {
        if (strtoupper($request->method()) === 'OPTIONS') {
            return response('', 200, [
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Allow-Origin' => $request->header('Origin', '*'),
                'Access-Control-Allow-Methods' => '*',
                'Access-Control-Allow-Headers' => '*',
            ]);
        }

        return $handler($request);
    }
}
