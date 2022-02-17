<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace Framework\Contract;

use Framework\Http\Request;
use Framework\Http\Response;
use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * @param Throwable $e
     * @return void
     */
    public function report(Throwable $e): void;

    /**
     * @param Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render(Request $request, Throwable $e): Response;
}
