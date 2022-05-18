<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Core;

use Framework\Contract\ExceptionHandlerInterface;
use Framework\Http\HttpRequest;
use Throwable;

/**
 * 默认异常处理类
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var array
     */
    public array $dontReport = [
    ];

    /**
     * @var bool
     */
    protected bool $debug = false;

    /**
     * ExceptionHandler constructor.
     * @param bool $debug
     */
    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function report(Throwable $e): void
    {
        if ($this->shouldntReport($e)) {
            return;
        }
        Log::error($e->getMessage(), ['exception' => (string)$e]);
    }

    public function render(HttpRequest $request, Throwable $e): mixed
    {
        $error = $this->debug ? nl2br((string)$e) : 'Server internal error';
        return \response($error, 500);
    }

    /**
     * 检查是否在dontReport数组中
     * @param $e
     * @return bool
     */
    private function shouldntReport($e): bool
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        return false;
    }
}
