<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
use framework\bootstrap\Container;
use framework\core\Config;
use framework\http\Response;
use Workerman\Protocols\Http\Response as WorkerResponse;

// 配置文件路径
if (! function_exists('config_path')) {
    function config_path(): string
    {
        return BASE_PATH . '/config';
    }
}
if (! function_exists('app_path')) {
    function app_path(): string
    {
        return BASE_PATH . '/app';
    }
}

if (! function_exists('runtime_path')) {
    function runtime_path(): string
    {
        return BASE_PATH . '/runtime';
    }
}

if (! function_exists('public_path')) {
    function public_path(): string
    {
        return BASE_PATH . '/public';
    }
}

/*
 * @param $template
 * @param array $vars
 * @return string
 */
if (! function_exists('view')) {
    function view($template, $vars = []): string
    {
        static $handler;
        if (null === $handler) {
            $handler = config('view.handler');
        }
        return $handler::render($template, $vars);
    }
}

// 分配变量
if (! function_exists('assign')) {
    function assign(string $name, $value): void
    {
        \framework\view\View::assign($name, $value);
    }
}

// 获取配置
if (! function_exists('config')) {
    function config($key = null, $default = null)
    {
        return Config::get($key, $default);
    }
}

/*
 * 打印一行
 * @param $msg
 */
if (! function_exists('print_line')) {
    function print_line($msg)
    {
        echo "{$msg} \n";
    }
}

/*
 * 终端高亮打印绿色
 * @param $message
 */
if (! function_exists('print_ok')) {
    function print_ok($message)
    {
        printf("\033[32m\033[1m{$message}\033[0m\n");
    }
}

/*
 * 终端高亮打印红色
 * @param $message
 */
if (! function_exists('print_error')) {
    function print_error($message)
    {
        printf("\033[31m\033[1m{$message}\033[0m\n");
    }
}

/*
 * 终端高亮打印黄色
 * @param $message
 */
if (! function_exists('print_warning')) {
    function print_warning($message)
    {
        printf("\033[33m\033[1m{$message}\033[0m\n");
    }
}

/*
 * @param $worker
 * @param $class
 */
if (! function_exists('worker_bind')) {
    function worker_bind($worker, $class)
    {
        $callbackMap = [
            'onConnect',
            'onMessage',
            'onClose',
            'onError',
            'onBufferFull',
            'onBufferDrain',
            'onWorkerStop',
            'onWebSocketConnect',
        ];
        foreach ($callbackMap as $name) {
            if (method_exists($class, $name)) {
                $worker->{$name} = [$class, $name];
            }
        }
        if (method_exists($class, 'onWorkerStart')) {
            call_user_func([$class, 'onWorkerStart'], $worker);
        }
    }
}
// @return int
if (! function_exists('cpu_count')) {
    function cpu_count(): int
    {
        if ('darwin' === strtolower(PHP_OS)) {
            $count = shell_exec('sysctl -n machdep.cpu.core_count');
        } else {
            $count = shell_exec('nproc');
        }
        return (int) $count > 0 ? (int) $count : 4;
    }
}

// 获取容器
if (! function_exists('container')) {
    function container()
    {
        return Container::instance();
    }
}

if (! function_exists('formatUrl')) {
    function formatUrl(string $url): string
    {
        return '/' . trim($url, '/');
    }
}

/*
 * 返回 response
 * @param int $status
 * @param array $headers
 * @param string $body
 * @return \framework\http\Response
 */
if (! function_exists('response')) {
    function response($body = '', $status = 200, $headers = []): Response
    {
        $response = Response::init(new WorkerResponse())->body($body)->status($status);
        foreach ($headers ?? [] as $name => $value) {
            $response->header($name, $value);
        }
        return $response;
    }
}
