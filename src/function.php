<?php
declare(strict_types=1);

/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
use Framework\Application;
use Framework\Contract\BootstrapInterface;
use Framework\Core\Container;
use Framework\Event\Event;
use Framework\Http\HttpRequest;
use Framework\Http\HttpResponse;
use Framework\View\HerosTemplate;
use Monda\Utils\Util\Config;

//env_config
if (! function_exists('env_config')) {
    function env_config(string $key, $default = null)
    {
        global $env;
        return $env->get($key, $default);
    }
}

/**
 * 公共方法
 */
if (! function_exists('config_path')) {
    function config_path(): string
    {
        return base_path() . '/config';
    }
}

if (! function_exists('app_path')) {
    function app_path(): string
    {
        return base_path() . '/app';
    }
}

/**
 * 运行环境
 */
if (! function_exists('runtime_path')) {
    function runtime_path(): string
    {
        return base_path() . '/runtime';
    }
}

if (! function_exists('public_path')) {
    function public_path(): string
    {
        return base_path() . '/public';
    }
}

/**
 * 基础路径
 */
if (! function_exists('base_path')) {
    function base_path(): string
    {
        return BASE_PATH;
    }
}

/**
 * 配置文件
 */
if (! function_exists('config')) {
    function config(string $key, $default = null)
    {
        return Config::get($key, $default);
    }
}

if (! function_exists('container')) {
    function container(string $clazz): mixed
    {
        return Container::instance()->get($clazz);
    }
}

/**
 * @param array $data
 * @param int $options
 * @return HttpResponse
 */
if (! function_exists('json')) {
    function json(array $data, int $options = JSON_UNESCAPED_UNICODE): HttpResponse
    {
        return response(json_encode($data, $options), 200, ['Content-Type' => 'application/json']);
    }
}

/**
 * @param $location
 * @param int $status
 * @param array $headers
 * @return HttpResponse
 */
if (! function_exists('redirect')) {
    function redirect(string $location, int $status = 302, array $headers = []): HttpResponse
    {
        $response = response('', $status, ['Location' => $location]);
        foreach ($headers ?? [] as $name => $value) {
            $response->header($name, $value);
        }
        return $response;
    }
}

/**
 * @param $worker
 * @param $class
 */
if (! function_exists('worker_bind')) {
    function worker_bind($worker, $class, $toWorkerStart = true)
    {
        $callbackMap = [
            'onConnect',
            'onMessage',
            'onClose',
            'onError',
            'onBufferFull',
            'onBufferDrain',
            'onWorkerStop',
            'onWebSocketConnect'
        ];
        foreach ($callbackMap as $name) {
            if (method_exists($class, $name)) {
                $worker->$name = [$class, $name];
            }
        }
        if ($toWorkerStart && method_exists($class, 'onWorkerStart')) {
            call_user_func([$class, 'onWorkerStart'], $worker);
        }
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
    function response($body = '', $status = 200, $headers = []): HttpResponse
    {
        $response = HttpResponse::init();
        $response->withBody($body)->withStatus($status);
        foreach ($headers ?? [] as $name => $value) {
            $response->header($name, $value);
        }
        return $response;
    }
}

/*
 * @param $template
 * @param array $vars
 * @return string
 */
if (! function_exists('view')) {
    function view(string $template, array $vars = []): string
    {
        return HerosTemplate::render($template, $vars);
    }
}

if (! function_exists('assign')) {
    function assign(string $name, mixed $value): void
    {
        HerosTemplate::assign($name, $value);
    }
}

if (! function_exists('request')) {
    function request(): HttpRequest
    {
        return Application::$request;
    }
}

if (! function_exists('worker_start')) {
    function worker_start(string $processName, array $config): void
    {
        $worker = new \Workerman\Worker($config['listen'] ?? null, $config['context'] ?? []);
        $property_map = [
            'count',
            'user',
            'group',
            'reloadable',
            'reusePort',
            'transport',
            'protocol',
        ];
        $worker->name = $processName;
        foreach ($property_map as $property) {
            if (isset($config[$property])) {
                $worker->$property = $config[$property];
            }
        }
        $worker->onWorkerStart = function ($worker) use ($config) {
            $bootStrapFiles = config('bootstrap', []);
            foreach ($bootStrapFiles ?? [] as $className) {
                /** @var BootstrapInterface $worker */
                $className::start($worker);
            }
            if (isset($config['handler'])) {
                if (! class_exists($config['handler'])) {
                    echo "process error: class {$config['handler']} not exists\r\n";
                    return;
                }
                $instance = Container::make($config['handler'], $config['constructor'] ?? []);
                worker_bind($worker, $instance);
            }
        };
    }
}

/**
 * 事件
 * @param $event
 */
if (! function_exists('event')) {
    function event($event)
    {
        Event::dispatch($event);
    }
}

/**
 * 检查端口是否可以被绑定
 * @author flynetcn
 */
if (! function_exists('check_port_bind_able')) {
    function check_port_bind_able(string $host, int $port, &$errno = null, &$errstr = null): bool
    {
        $socket = @stream_socket_server("tcp://$host:$port", $errno, $errstr);
        if (! $socket) {
            return false;
        }
        fclose($socket);
        unset($socket);
        return true;
    }
}

/**
 * Phar support.
 * Compatible with the 'realpath' function in the phar file.
 *
 * @param string $file_path
 * @return string
 */
if (! function_exists('get_real_path')) {
    function get_real_path(string $filePath): string
    {
        if (str_starts_with($filePath, 'phar://')) {
            return $filePath;
        }
        return realpath($filePath);
    }
}

/**
 * @return bool
 */
if (! function_exists('is_phar')) {
    function is_phar(): bool
    {
        return class_exists(\Phar::class, false) && Phar::running();
    }
}

/**
 * @param mixed $key
 * @param mixed $default
 * @return mixed
 */
if (! function_exists('session')) {
    function session(?string $key = null, $default = null)
    {
        $session = request()->session();
        if (null === $key) {
            return $session;
        }
        if (\strpos($key, '.')) {
            $key_array = \explode('.', $key);
            $value = $session->all();
            foreach ($key_array as $index) {
                if (! isset($value[$index])) {
                    return $default;
                }
                $value = $value[$index];
            }
            return $value;
        }
        return $session->get($key, $default);
    }
}
