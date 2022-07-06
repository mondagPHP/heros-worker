<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Contract;

use Framework\Http\HttpRequest;

/**
 * 中间件
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param  HttpRequest  $request httpRequest
     * @param  callable  $handler 闭包
     */
    public function process(HttpRequest $request, callable $handler);
}
