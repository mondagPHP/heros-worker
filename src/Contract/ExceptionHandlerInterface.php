<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Contract;

use Framework\Http\Request;
use Throwable;

interface ExceptionHandlerInterface
{
    public function report(Throwable $e): void;

    public function render(Request $request, Throwable $e);
}
