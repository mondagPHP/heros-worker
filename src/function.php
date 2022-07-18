<?php

declare(strict_types=1);

/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

use Framework\Application;
use Framework\Contract\BootstrapInterface;
use Framework\Core\Container;
use Framework\Database\HeroModel;
use Framework\Http\HttpRequest;
use Framework\Http\HttpResponse;
use Framework\View\HerosTemplate;
use Illuminate\Support\Arr;
use Monda\Utils\String\StringUtil;
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
        return base_path().DIRECTORY_SEPARATOR.'config';
    }
}

if (! function_exists('app_path')) {
    function app_path(): string
    {
        return base_path().DIRECTORY_SEPARATOR.'app';
    }
}

/**
 * 运行环境
 */
if (! function_exists('runtime_path')) {
    function runtime_path(): string
    {
        return base_path().DIRECTORY_SEPARATOR.'runtime';
    }
}

if (! function_exists('public_path')) {
    function public_path(): string
    {
        return base_path().DIRECTORY_SEPARATOR.'public';
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
 * @param  array  $data
 * @param  int  $options
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
 * @param  int  $status
 * @param  array  $headers
 * @return HttpResponse
 */
if (! function_exists('redirect')) {
    function redirect(string $location, int $status = 302, array $headers = []): HttpResponse
    {
        $response = response('', $status, ['Location' => $location]);
        foreach ($headers as $name => $value) {
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
            'onWebSocketConnect',
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
 * 检查端口是否可以被绑定
 *
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
 * @param  string  $file_path
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
 * @param  mixed  $key
 * @param  mixed  $default
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

if (! function_exists('searchForTopParent')) {
    function searchForTopParent(ReflectionClass $class, string $attributeName): bool
    {
        //当前有就可以马上停止
        if (count($class->getAttributes($attributeName)) > 0) {
            return true;
        }
        //找父类
        while ($class->getParentClass() !== false) {
            $parentClass = $class->getParentClass();
            if (count($parentClass->getAttributes($attributeName)) > 0) {
                return true;
            }
            $class = $class->getParentClass();
        }

        return false;
    }
}

/**
 * notice:注意修改，数据存在的数据会直接覆盖
 *
 * @param  HeroModel  $clazz 模型类
 * @param  array  $arr 自动进行转化
 * @return bool
 */
if (! function_exists('quickCreateOrUpdate')) {
    /** @noinspection PhpUndefinedMethodInspection */
    function quickCreateOrUpdate(string $clazz, array $arr): bool
    {
        $fillArr = [];
        foreach ($arr ?? [] as $key => $value) {
            $fillArr[StringUtil::hump2Underline($key)] = $value;
        }
        if (! $fillArr) {
            return false;
        }
        if (isset($fillArr['id'])) {
            $updateArr = Arr::only($fillArr, (new $clazz())->getFillable());
            unset($updateArr['id']);
            $result = (bool) $clazz::query()->where('id', $fillArr['id'])->update($updateArr);
        } else {
            $result = (bool) $clazz::query()->create($fillArr);
        }

        return $result;
    }
}
