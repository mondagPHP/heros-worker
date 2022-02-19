<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Http;

use Workerman\Protocols\Http\Request as WorkerRequest;

/**
 * Class Request
 * @package Framework\Http
 * @method mixed get(?string $name = null, $default = null)
 * @method mixed post(?string $name = null, $default = null)
 * @method mixed header(?string $name = null, $default = null)
 * @method mixed cookie(?string $name = null, $default = null)
 * @method mixed file(?string $name = null)
 * @method string method()
 * @method string protocolVersion()
 * @method string host(bool $withoutPort = false)
 * @method string uri()
 * @method string path()
 * @method string queryString()
 * @method string sessionId()
 * @method string rawHead()
 * @method string rawBody()
 * @method string rawBuffer()
 */
class Request
{
    private array $params;

    private array $injectObject = [];

    private WorkerRequest $request;

    private Session $session;

    private function __construct(WorkerRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $name
     * @param $arguments
     * @return mixed
     */
    public function __call(string $name, $arguments)
    {
        return $this->request->{$name}(...$arguments);
    }

    /**
     * @param WorkerRequest $request
     * @param Session $session
     * @return static
     */
    public static function init(WorkerRequest $request, Session $session): self
    {
        $httpRequest = new self($request);
        $httpRequest->setParams($request->get() + $request->post());
        $httpRequest->setSession($session);
        return $httpRequest;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function getParameter(string $name, $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @param Session $session
     */
    public function setSession(Session $session): void
    {
        $this->session = $session;
    }

    /**
     * @param object $object
     * @return void
     */
    public function pushInjectObject(object $object)
    {
        $this->injectObject[get_class($object)] = $object;
    }

    /**
     * @return array
     */
    public function getInjectObject(): array
    {
        return array_values($this->injectObject);
    }
}
