<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Http;

use Workerman\Protocols\Http\Request;

/**
 * Class Request
 * @package Framework\Http
 */
class HttpRequest extends Request
{
    private array $params;

    private array $injectObject = [];

    /**
     * @param Request $request
     * @return static
     */
    public static function init(Request $request): self
    {
        $httpRequest = new self($request);
        //get、post to set
        $httpRequest->setParams($request->get() + $request->post());
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
