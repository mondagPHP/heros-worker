<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\exception;

use framework\http\Request;
use framework\http\Response;
use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * @return mixed
     */
    public function report(Throwable $e);

    public function render(Request $request, Throwable $e): Response;
}
