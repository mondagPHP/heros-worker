<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\http;

use Workerman\Protocols\Http\Request as WorkerRequest;

/**
 * Class Request.
 */
class Request
{
    //全部参数
    private $params;
    //获得原始请求post包体
    private $rawBody;
    //获取header
    private $header;
    //获取cookie
    private $cookie;
    //获取特定上传文件
    private $file;
    //获取host
    private $host;
    //获取请求方法
    private $method;
    //获取请求uri
    private $uri;
    //获取请求路径
    private $path;
    //获取请求queryString
    private $queryString;
    //获取请求HTTP版本
    private $protocolVersion;
    //获取请求sessionId
    private $sessionId;
    //原生request对象
    private $workerRequest;
    private $ip;
    private $port;

    /**
     * Session constructor.
     * @param $params
     * @param $rawBody
     * @param $header
     * @param $cookie
     * @param $file
     * @param $host
     * @param $method
     * @param $uri
     * @param $path
     * @param $queryString
     * @param $protocolVersion
     * @param $sessionId
     * @param $ip
     * @param $port
     */
    public function __construct($params, $rawBody, $header, $cookie, $file, $host, $method, $uri, $path, $queryString, $protocolVersion, $sessionId, $ip, $port)
    {
        $this->params = $params;
        $this->rawBody = $rawBody;
        $this->header = $header;
        $this->cookie = $cookie;
        $this->file = $file;
        $this->host = $host;
        $this->method = $method;
        $this->uri = $uri;
        $this->path = $path;
        $this->queryString = $queryString;
        $this->protocolVersion = $protocolVersion;
        $this->sessionId = $sessionId;
        $this->ip = $ip;
        $this->port = $port;
    }

    public static function init(WorkerRequest $request, string $ip, int $port): self
    {
        $params = $request->get() + $request->post();
        $request = new self(
            $params,
            $request->rawBody(),
            $request->header(),
            $request->cookie(),
            $request->file(),
            $request->host(),
            $request->method(),
            $request->uri(),
            $request->path(),
            $request->queryString(),
            $request->protocolVersion(),
            $request->sessionId(),
            $ip,
            $port
        );
        $request->setWorkerRequest($request);
        return $request;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params): void
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getRawBody()
    {
        return $this->rawBody;
    }

    /**
     * @param mixed $rawBody
     */
    public function setRawBody($rawBody): void
    {
        $this->rawBody = $rawBody;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param mixed $header
     */
    public function setHeader($header): void
    {
        $this->header = $header;
    }

    /**
     * @return mixed
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @param mixed $cookie
     */
    public function setCookie($cookie): void
    {
        $this->cookie = $cookie;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host): void
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method): void
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri): void
    {
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @param mixed $queryString
     */
    public function setQueryString($queryString): void
    {
        $this->queryString = $queryString;
    }

    /**
     * @return mixed
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @param mixed $protocolVersion
     */
    public function setProtocolVersion($protocolVersion): void
    {
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param mixed $sessionId
     */
    public function setSessionId($sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return mixed
     */
    public function getWorkerRequest()
    {
        return $this->workerRequest;
    }

    /**
     * @param mixed $workerRequest
     */
    public function setWorkerRequest($workerRequest): void
    {
        $this->workerRequest = $workerRequest;
    }

    public function getParameter(string $name, $default = null)
    {
        return isset($this->params[$name]) ? $this->queryString[$name] : $default;
    }

    /**
     * 设置参数.
     * @param $value
     */
    public function setParameter(string $name, $value): void
    {
        $this->params[$name] = $value;
    }

    public function fullUrl(): string
    {
        return '//' . $this->host . $this->uri;
    }

    public function isAjax(): bool
    {
        return 'XMLHttpRequest' === $this->workerRequest->header('X-Requested-With');
    }

    public function isPjax(): bool
    {
        return (bool) $this->workerRequest->header('X-PJAX');
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }
}
