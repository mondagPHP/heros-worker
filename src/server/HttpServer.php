<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\server;

use ErrorException;
use FastRoute\Dispatcher;
use framework\App;
use framework\boot\RouterCollector;
use framework\bootstrap\Log;
use framework\exception\ExceptionHandlerInterface;
use framework\exception\RequestMethodException;
use framework\exception\RouteNotFoundException;
use framework\http\Request as HttpRequest;
use framework\http\Response as HttpResponse;
use framework\http\Session;
use ReflectionException;
use Throwable;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

/**
 * Class HttpServer.
 */
class HttpServer
{
    /**
     * @var Worker
     */
    private $worker;

    /**
     * 路由调度.
     */
    private $dispatcher;

    /**
     * 默认配置.
     * @var array
     */
    private $config;

    private static $_request = null;

    /**
     * callback.
     * @var string[]
     */
    private $callbackMap = [
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onWorkerStop',
        'onWebSocketConnect',
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge(['listen' => 'http://127.0.0.1:8080', 'count' => 2, 'context' => [], 'name' => 'monda-php-worker'], $config);
        $this->worker = new Worker($this->config['listen'], $this->config['context']);
        $this->worker->reloadable = true;
        //设置属性
        $propertyMap = ['name', 'count', 'user', 'group', 'reusePort', 'transport'];
        foreach ($propertyMap as $property) {
            if (isset($config[$property])) {
                $this->worker->{$property} = $config[$property];
            }
        }
        foreach ($this->callbackMap as $name) {
            if (method_exists($this, $name)) {
                $this->worker->{$name} = [$this, $name];
            }
        }
        $this->worker->onWorkerStart = [$this, 'onWorkerStart'];
    }

    /**
     * @throws \ErrorException|ReflectionException
     * @throws \Exception
     */
    public function onWorkerStart(Worker $worker)
    {
        //注册错误
        set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        });
        register_shutdown_function(function ($start_time) {
            if (time() - $start_time <= 1) {
                sleep(1);
            }
        }, time());

        $bootStrapFiles = config('bootstrap');
        foreach ($bootStrapFiles as $className) {
            // @var Bootstrap $className
            $className::start($worker);
        }
        if (isset($this->config['services'])) {
            //listen
            foreach ($this->config['services'] ?? [] as $server) {
                $listen = new Worker($server['listen'] ?? null, $server['context'] ?? []);
                $class = container()->make($server['handler'], $server['constructor'] ?? []);
                worker_bind($listen, $class);
                $listen->listen();
            }
        }

        App::init();
        $this->dispatcher = container()->get(RouterCollector::class)->getDispatcher();
        //闭包
        if (isset($this->config['onWorkerStart']) && is_callable($this->config['onWorkerStart'])) {
            $this->config['onWorkerStart']($worker);
        }
    }

    public function onMessage(TcpConnection $connection, Request $request)
    {
        //尝试更新
        static::tryFreshWorker();

        //注册框架的http
        $httpSession = Session::init($request);
        $httpRequest = HttpRequest::init($connection, $request, $httpSession);
        $httpResponse = HttpResponse::init(new Response(200));
        static::$_request = $httpRequest;

        //跨域返回空
        $corsConfig = config('cors') ?? [];
        if (isset($corsConfig['enable']) && $corsConfig['enable'] && strtoupper($request->method()) === 'OPTIONS') {
            self::send($connection, $httpResponse->body('')->end(), $request);
            return;
        }

        try {
            $routeInfo = $this->dispatcher->dispatch($httpRequest->getMethod(), $httpRequest->getPath());
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    //静态资源文件
                    if ($path = $this->findFile($httpRequest->getPath())) {
                        //禁止访问.开头的隐藏文件
                        if (false !== strpos($path, '/.')) {
                            self::send($connection, $httpResponse->body('<h1>403 forbidden</h1>')->status(403)->end(), $request);
                            return;
                        }
                        self::send($connection, $httpResponse->status(200)->file($path)->end(), $request);
                        return;
                    }
                    throw new RouteNotFoundException("找不到路由:{$httpRequest->getUri()}");
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    throw new RequestMethodException('request method not allow!');
                    break;
                case Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = array_merge($httpRequest->getParams(), $routeInfo[2]);
                    $extVars = [$httpRequest, $httpResponse, $httpSession, $connection];
                    $responseObj = $handler($httpRequest, $vars, $extVars);
                    //框架的httpResponse 直接end
                    if ($responseObj instanceof HttpResponse) {
                        self::send($connection, $responseObj->end(), $request);
                    //workerman response
                    } elseif ($responseObj instanceof Response) {
                        self::send($connection, $responseObj, $request);
                    } else {
                        self::send($connection, $httpResponse->body($responseObj)->end(), $request);
                    }
                    return;
            }
        } catch (Throwable $exception) {
            self::send($connection, $this->exceptionResponse($exception, $httpRequest, $httpResponse)->end(), $request);
            return;
        }
    }

    public static function request()
    {
        return static::$_request;
    }

    /**
     * @param \Workerman\Connection\TcpConnection $connection
     * @param \Workerman\Protocols\Http\Response $response
     * @param \Workerman\Protocols\Http\Request $request
     */
    protected static function send(TcpConnection $connection, Response $response, Request $request)
    {
        //跨域
        $cors = config('cors', null) ?? [];
        if (isset($cors['enable']) && $cors['enable'] && is_array($cors)) {
            foreach ($cors ?? [] as $key => $value) {
                if ($key === 'enable') {
                    continue;
                }
                $response->header($key, $value);
            }
        }
        $keepAlive = $request->header('connection');
        if ((null === $keepAlive && '1.1' === $request->protocolVersion()) || 'keep-alive' === $keepAlive || 'Keep-Alive' === $keepAlive) {
            $connection->send($response);
            return;
        }
        $connection->close($response);
        return;
    }

    protected static function tryFreshWorker(): void
    {
        static $requestCount;
        // 业务处理略
        if (++$requestCount > config('server.max_request', 100000)) {
            Worker::stopAll();
        }
    }

    /**
     * @return Response|string
     */
    private function exceptionResponse(Throwable $e, HttpRequest $request, HttpResponse $response): HttpResponse
    {
        try {
            $exceptionHandlerClass = config('exception.default');
            /** @var ExceptionHandlerInterface $exceptionHandler */
            $exceptionHandler = container()->make($exceptionHandlerClass, ['logger' => Log::channel(), 'debug' => config('app.debug')]);
            $exceptionHandler->report($e);
            return $exceptionHandler->render($request, $e);
        } catch (Throwable $e) {
            return $response->body((string)$e);
        }
    }

    /**
     * 静态资源文件.
     * @param $path
     * @return false|string
     */
    private function findFile($path)
    {
        $file = \realpath(public_path() . '/' . trim($path, '/'));
        if (false === $file || false === \is_file($file)) {
            return false;
        }
        return $file;
    }
}
