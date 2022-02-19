<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Component;

use Framework\Annotation\Component;
use Framework\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;
use function sprintf;
use function str_replace;

#[Component]
class StdoutLogger implements StdoutLoggerInterface
{
    /**
     * array
     * @var string[]
     */
    private array $logLevel;

    public function __construct()
    {
        $this->logLevel = config('app.debug') ? ['debug', 'info', 'emergency', 'alert', 'critical', 'error', 'warning', 'notice'] : ['critical', 'error'];
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        if (! in_array($level, $this->logLevel, true)) {
            return;
        }
        $keys = array_keys($context);
        $search = array_map(static function ($key) {
            return sprintf('{%s}', $key);
        }, $keys);
        $message = str_replace($search, $context, $this->getMessage((string)$message, $level));
        $this->stdout(date('Y-m-d H:i:s') . ' ' . $message);
    }

    /**
     * 格式化信息.
     */
    protected function getMessage(string $message, string $level): string
    {
        $color = match ($level) {
            LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR => "\033[38;5;1m[%s]\033[0m",
            LogLevel::WARNING, LogLevel::NOTICE => "\033[33;5;1m[%s]\033[0m",
            LogLevel::DEBUG => "\033[30;5;1m[%s]\033[0m",
            default => "\033[32;5;1m[%s]\033[0m",
        };
        $template = sprintf($color, strtoupper($level));
        return sprintf($template . ' %s', $message);
    }

    /**
     * @param string $c
     */
    private function stdout(string $c): void
    {
        if (is_resource(STDOUT)) {
            fwrite(STDOUT, $c . PHP_EOL);
        }
    }
}
