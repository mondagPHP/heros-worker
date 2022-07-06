<?php

declare(strict_types=1);

namespace Framework\RateLimit;

use Framework\Contract\MiddlewareInterface;
use Framework\Http\HttpRequest;

class LimitTrafficMiddleware implements MiddlewareInterface
{
    public function process(HttpRequest $request, callable $handler): mixed
    {
        if ($result = RateLimit::traffic()) {
            return response(json_encode($result['response']), $result['httpStatus'], [
                'Content-Type' => 'application/json',
                'X-Rate-Limit-Limit' => $result['limit'],
                'X-Rate-Limit-Remaining' => $result['remaining'],
                'X-Rate-Limit-Reset' => $result['reset'],
            ]);
        }

        return $handler($request);
    }
}
