<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Contract;

use Framework\Http\HttpRequest;
use Throwable;

interface ExceptionHandlerInterface
{
    public function report(Throwable $e): void;

    public function render(HttpRequest $request, Throwable $e): mixed;
}
