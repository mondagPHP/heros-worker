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
use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var array
     */
    public $ignoreReport = [
    ];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * ExceptionHandler constructor.
     * @param $logger
     * @param $debug
     */
    public function __construct($logger, $debug)
    {
        $this->logger = $logger;
        $this->debug = $debug;
    }

    public function report(Throwable $e)
    {
        if (! $this->ignoreReport($e)) {
            $this->logger->error($e->getMessage(), ['exception' => (string) $e]);
        }
    }

    public function render(Request $request, Throwable $e): Response
    {
        if (\method_exists($e, 'render')) {
            return $e->render();
        }
        $error = $this->debug ? nl2br((string) $e) : 'Server internal error';
        return \response($error, 500, []);
    }

    /**
     * 是否忽略.
     */
    protected function ignoreReport(Throwable $e): bool
    {
        foreach ($this->ignoreReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        return false;
    }
}
