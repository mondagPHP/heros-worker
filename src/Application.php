<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework;

use ErrorException;
use FastRoute\Dispatcher;
use Framework\Component\RouterCollector;
use Framework\Contract\BootstrapInterface;
use Framework\Contract\JsonAble;
use Framework\Core\Container;
use Framework\Core\ExceptionHandler;
use Framework\Core\Log;
use Framework\Core\Scanner;
use Framework\Exception\FileNotFoundException;
use Framework\Exception\HerosException;
use Framework\Exception\RequestMethodException;
use Framework\Http\HttpRequest;
use Framework\Http\HttpResponse;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Timer;
use Workerman\Worker;

/**
 * Class Application
 * @package Framework
 * 入口
 */
class Application
{
    public const VERSION = '2.1.0';

    /**
     * @var HttpRequest
     */
    public static HttpRequest $request;

    public static TcpConnection $connection;

    /**
     * @var array $config
     */
    protected array $config;

    /**
     * @var Worker $worker
     */
    protected Worker $worker;

    /**
     * @var mixed
     */
    protected mixed $dispatcher;

    /**
     * 请求次数
     * @var int
     */
    protected static int $_maxRequestCount = 10000;

    /**
     * @var int|null
     */
    protected static ?int $_gracefulStopTimer = null;

    /**
     * @var array 闭包mappings
     */
    protected static array $handlerMappings = [];

    /**
     * @return void
     */
    public function run(): void
    {
        $this->init();
        //增加默认值,
        $this->config = config('server', [
            'listen' => 'http://0.0.0.0:8080',
            'context' => [],
            'reloadable' => 'true',
            'max_request' => -1,
        ]);
        $this->worker = new Worker($this->config['listen'], $this->config['context']);
        $this->worker->reloadable = $this->config['reloadable'] ?: true;
        $maxRequestCount = (int)$this->config['max_request'];
        if ($maxRequestCount > 0) {
            static::$_maxRequestCount = $maxRequestCount;
        }
        $propertyMap = ['name', 'count', 'user', 'group', 'reusePort', 'transport','protocol'];
        foreach ($propertyMap as $property) {
            if (isset($this->config[$property])) {
                $this->worker->{$property} = $this->config[$property];
            }
        }
        $this->worker->onWorkerStart = [$this, 'onWorkerStart'];
        worker_bind($this->worker, $this, false);
    }

    /**
     * @throws ErrorException
     * @throws \Exception
     */
    public function onWorkerStart(Worker $worker)
    {
        set_error_handler(static function ($level, $message, $file = '', $line = 0, $context = []) {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        });
        register_shutdown_function(static function ($start_time) {
            if (time() - $start_time <= 1) {
                sleep(1);
            }
        }, time());
        $bootStrapFiles = config('bootstrap', []);
        foreach ($bootStrapFiles ?? [] as $className) {
            /** @var BootstrapInterface $worker */
            $className::start($worker);
        }
        Scanner::begin();
        $this->dispatcher = container(RouterCollector::class)->getDispatcher();
        Http::requestClass(HttpRequest::class);
    }

    /**
     * @throws \Exception
     */
    public function onMessage(TcpConnection $connection, HttpRequest $httpRequest): void
    {
        static $requestCount = 0;
        static::$request = $httpRequest;
        static::$connection = $connection;
        if (++$requestCount > static::$_maxRequestCount) {
            $this->tryToGracefulExit();
        }
        try {
            $requestPath = $httpRequest->path();
            //做了一层缓存，加快响应
            if (isset(static::$handlerMappings[$requestPath])) {
                $httpRequest->setRouterParams(static::$handlerMappings[$requestPath]['params']);
                $cacheHandler = static::$handlerMappings[$requestPath]['handler'];
                $response = static::handlerRequestResult($cacheHandler($httpRequest));
                static::send($connection, $response, $httpRequest);
                return;
            }
            $routeInfo = $this->dispatcher->dispatch($httpRequest->method(), $requestPath);
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    $path = $this->findFile($httpRequest->path());
                    if (! $path) {
                        throw new FileNotFoundException("path not found:{$httpRequest->path()}");
                    }
                    if (str_contains($path, '/.')) {
                        throw new HerosException('403 forbidden');
                    }
                    if ($this->notModifiedSince($httpRequest, $path)) {
                        $response = \response('', 304);
                    } else {
                        $response = \response()->withFile($path);
                    }
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    throw new RequestMethodException('request method not allow!');
                case Dispatcher::FOUND:
                    $httpRequest->setRouterParams($routeInfo[2]);
                    $handler = $routeInfo[1];
                    $response = static::handlerRequestResult($handler($httpRequest));
                    static::$handlerMappings[$requestPath] = [
                        'handler' => $routeInfo[1],
                        'params' => $routeInfo[2],
                    ];
                    break;
                default:
                    throw new HerosException("fast route error.{$httpRequest->path()}!");
            }
            static::send($connection, $response, $httpRequest);
        } catch (\Throwable $exception) {
            static::send($connection, static::handlerRequestResult(static::exceptionResponse($exception, $httpRequest)), $httpRequest);
        }
    }

    /**
     * @param Request $request
     * @param string $file
     * @return bool
     */
    protected function notModifiedSince(Request $request, string $file): bool
    {
        $ifModifiedSince = $request->header('if-modified-since');
        if ($ifModifiedSince === null || ! ($mtime = \filemtime($file))) {
            return false;
        }
        return $ifModifiedSince === \gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    }

    /**
     * 返回给前端
     * @param TcpConnection $connection
     * @param $response
     * @param Request $request
     * @return void
     */
    protected static function send(TcpConnection $connection, $response, Request $request)
    {
        $keepAlive = $request->header('connection');
        if (($keepAlive === null && $request->protocolVersion() === '1.1')
            || $keepAlive === 'keep-alive' || $keepAlive === 'Keep-Alive'
        ) {
            $connection->send($response);
            return;
        }
        $connection->close($response);
    }

    /**
     * 定时器关闭，防止马上触发stopALL导致无法访问
     * @throws \Exception
     */
    protected function tryToGracefulExit(): void
    {
        if (static::$_gracefulStopTimer === null) {
            static::$_gracefulStopTimer = Timer::add(random_int(1, 10), function () {
                if (\count($this->worker->connections) === 0) {
                    static::$handlerMappings = [];
                    Worker::stopAll();
                }
            });
        }
    }

    /**
     * @return void
     */
    private function init(): void
    {
        ini_set('session.gc_maxlifetime', (string)\config('session.gc_maxlifetime', '86400'));
        ini_set('session.cookie_lifetime', (string)\config('session.cookie_lifetime', '86400'));
        $serverConfig = config('server', []);
        $pidDir = dirname($serverConfig['pid_file']);
        if (! file_exists($pidDir) || ! is_dir($pidDir)) {
            if (! mkdir($pidDir, 0777, true)) {
                throw new \RuntimeException('Failed to create pidDir logs directory. Please check the permission.');
            }
        }
        $stdoutLogDir = dirname($serverConfig['stdout_file']);
        if (! file_exists($stdoutLogDir) || ! is_dir($stdoutLogDir)) {
            if (! mkdir($stdoutLogDir, 0777, true)) {
                throw new \RuntimeException('Failed to create runtime logs directory. Please check the permission.');
            }
        }
        //基本配置
        Worker::$pidFile = $serverConfig['pid_file'];
        Worker::$stdoutFile = $serverConfig['stdout_file'];
        Worker::$logFile = $serverConfig['log_file'];
        TcpConnection::$defaultMaxPackageSize = $serverConfig['max_package_size'] ?? 10 * 1024 * 1024;
        if (property_exists(Worker::class, 'statusFile')) {
            Worker::$statusFile = $serverConfig['status_file'] ?? '';
        }
        if (property_exists(Worker::class, 'stopTimeout')) {
            Worker::$stopTimeout = $serverConfig['stop_timeout'] ?? 2;
        }
    }

    /**
     * @param mixed $responseObj
     * @return Response|HttpResponse
     */
    private static function handlerRequestResult(mixed $responseObj): Response|HttpResponse
    {
        if ($responseObj instanceof Response) {
            $response = $responseObj;
        } else {
            if ($responseObj instanceof JsonAble) {
                $response = \response($responseObj->toJson(), 200, ['Content-Type' => 'application/json']);
            } elseif (is_array($responseObj)) {
                $response = \response(json_encode($responseObj, JSON_UNESCAPED_UNICODE), 200, ['Content-Type' => 'application/json']);
            } else {
                $response = \response($responseObj);
            }
        }
        return $response;
    }

    /**
     * @param \Throwable $e
     * @param HttpRequest $request
     * @return mixed
     */
    private static function exceptionResponse(\Throwable $e, Request $request): mixed
    {
        try {
            /** @var ExceptionHandler $exceptionHandler */
            if (class_exists("App\Exception\Handler")) {
                $exceptionHandler = Container::make("App\Exception\Handler", [config('app.debug')]);
            } else {
                $exceptionHandler = Container::make(ExceptionHandler::class, [config('app.debug')]);
            }
            $exceptionHandler->report($e);
            return $exceptionHandler->render($request, $e);
        } catch (\Throwable $e) {
            //最后系统兜底处理异常
            $message = ! config('app.debug') ? '系统出小差!' : $e->getMessage();
            Log::error('application:' . $e->getMessage());
            return \response($message, 500, []);
        }
    }

    /**
     * 静态资源文件.
     * @param string $path
     * @return false|string
     */
    private function findFile(string $path): bool|string
    {
        $file = \realpath(public_path() . '/' . trim($path, '/'));
        if (! $file) {
            return false;
        }
        if (! str_starts_with($file, public_path())) {
            return false;
        }
        if (false === \is_file($file)) {
            return false;
        }
        return $file;
    }
}
