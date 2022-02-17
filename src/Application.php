<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */

namespace Framework;

use ErrorException;
use FastRoute\Dispatcher;
use Framework\Component\RouterCollector;
use Framework\Contract\BootstrapInterface;
use Framework\Core\Container;
use Framework\Core\ExceptionHandler;
use Framework\Core\Log;
use Framework\Core\Scanner;
use Framework\Exception\HerosException;
use Framework\Exception\RequestMethodException;
use Framework\Exception\RouteNotFoundException;
use Framework\Http\Request as HttpRequest;
use Framework\Http\Response as HttpResponse;
use Framework\Http\Session;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request as WorkerRequest;
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Response as WorkerResponse;
use Workerman\Timer;
use Workerman\Worker;

/**
 * Class Application
 * @package Framework
 * 入口
 */
class Application
{
    public const VERSION = '2.0.0';
    /**
     * @var HttpRequest
     */
    public static HttpRequest $request;

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
     * callback.
     * @var string[]
     */
    private array $callbackMap = [
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onWorkerStop',
        'onWebSocketConnect',
    ];

    public function run()
    {
        $this->config = config('server', []);
        $this->worker = new Worker($this->config['listen'], $this->config['context']);
        $this->worker->reloadable = true;
        //默认值
        $maxRequestCount = (int)$this->config['max_request'];
        if ($maxRequestCount > 0) {
            static::$_maxRequestCount = $maxRequestCount;
        }
        //设置属性
        $propertyMap = ['name', 'count', 'user', 'group', 'reusePort', 'transport'];
        foreach ($propertyMap as $property) {
            if (isset($this->config[$property])) {
                $this->worker->{$property} = $this->config[$property];
            }
        }
        worker_bind($this->worker, $this);
    }

    /**
     * @throws ErrorException
     * @throws \Exception
     */
    public function onWorkerStart(Worker $worker)
    {
        //注册错误
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
        $this->dispatcher = container()->get(RouterCollector::class)->getDispatcher();
        //闭包
        if (isset($this->config['init_server']) && is_callable($this->config['init_server'])) {
            $this->config['init_server']($this->worker);
        }
    }

    /**
     * @throws \Exception
     */
    public function onMessage(TcpConnection $connection, WorkerRequest $request)
    {
        static $requestCount = 0;
        //init session request response
        $httpSession = Session::init($request);
        $httpRequest = HttpRequest::init($request, $httpSession);
        static::$request = $httpRequest;
        $httpResponse = HttpResponse::init();
        if (++$requestCount > static::$_maxRequestCount) {
            $this->tryToGracefulExit();
        }
        try {
            $routeInfo = $this->dispatcher->dispatch($httpRequest->method(), $httpRequest->path());
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    //静态资源文件
                    $path = $this->findFile($httpRequest->path());
                    if (!$path) {
                        Log::debug("route not found:{$httpRequest->path()}!");
                        throw new RouteNotFoundException("route not found:{$httpRequest->path()}");
                    }
                    //禁止访问.开头的隐藏文件
                    if (str_contains($path, '/.')) {
                        $returnResponse = $httpResponse->originResponse()->withBody('<h1>403 forbidden</h1>')->withStatus(403);
                    } else {
                        $returnResponse = $httpResponse->originResponse()->withStatus(200)->withFile($path);
                    }
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    Log::error("request method not allow.{$httpRequest->path()}!");
                    throw new RequestMethodException('request method not allow!');
                case Dispatcher::FOUND:
                    $vars = array_merge($request->get() + $request->post(), $routeInfo[2]);
                    $httpRequest->setParams($vars);
                    $handler = $routeInfo[1];
                    $responseObj = $handler($httpRequest);
                    if ($responseObj instanceof HttpResponse) {
                        $returnResponse = $responseObj->originResponse();
                    } elseif ($responseObj instanceof Response) {
                        $returnResponse = $responseObj;
                    } else {
                        if (is_object($responseObj)) {
                            if (method_exists($responseObj, '__toString')) {
                                $responseObj = (string)$responseObj;
                            } else {
                                $responseObj = json_encode($responseObj, JSON_UNESCAPED_UNICODE);
                            }
                            $httpResponse->originResponse()->header('Content-Type', 'application/json');
                        }
                        if (is_array($responseObj)) {
                            $responseObj = json_encode($responseObj, JSON_UNESCAPED_UNICODE);
                            $httpResponse->originResponse()->header('Content-Type', 'application/json');
                        }
                        $returnResponse = $httpResponse->originResponse()->withBody($responseObj);
                    }
                    break;
                default:
                    Log::error("fast route error.{$httpRequest->path()}!");
                    throw new HerosException("fast route error.{$httpRequest->path()}!");
            }
            self::send($connection, $returnResponse, $request);
            return;
        } catch (\Throwable $exception) {
            static::send($connection, static::exceptionResponse($exception, $httpRequest), $request);
        }
    }

    /**
     * 返回给前端
     * @param TcpConnection $connection
     * @param WorkerResponse $response
     * @param WorkerRequest $request
     */
    protected static function send(TcpConnection $connection, WorkerResponse $response, WorkerRequest $request)
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
                    Worker::stopAll();
                }
            });
        }
    }

    /**
     * @param \Throwable $e
     * @param HttpRequest $request
     * @return WorkerResponse
     */
    private static function exceptionResponse(\Throwable $e, HttpRequest $request): WorkerResponse
    {
        try {
            /** @var ExceptionHandler $exceptionHandler */
            $exceptionHandler = Container::make(config('exception.handler', ExceptionHandler::class), [config('app.debug')]);
            $exceptionHandler->report($e);
            $httpResponse = $exceptionHandler->render($request, $e);
            return $httpResponse->originResponse();
        } catch (\Throwable $e) {
            $message = config('app.debug') ? (string)$e : $e->getMessage();
            return \response($message, 500, [])->originResponse();
        }
    }

    /**
     * 静态资源文件.
     * @param $path
     * @return false|string
     */
    private function findFile($path): bool|string
    {
        $file = \realpath(public_path() . '/' . trim($path, '/'));
        if (!$file) {
            return false;
        }
        if (!str_starts_with($file, public_path())) {
            return false;
        }
        if (false === \is_file($file)) {
            return false;
        }
        return $file;
    }
}
