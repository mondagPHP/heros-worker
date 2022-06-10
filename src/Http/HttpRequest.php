<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Http;

use Workerman\Protocols\Http\Request;

/**
 * Class Request
 * @package Framework\Http
 */
class HttpRequest extends Request
{
    private array $routerParams = [];

    private array $injectObject = [];

    /**
     * @return array
     */
    public function getParams(): array
    {
        return array_merge($this->get(), $this->post(), $this->routerParams);
    }

    /**
     * @return array
     */
    public function getRouterParams(): array
    {
        return $this->routerParams;
    }

    /**
     * @param array $routerParams
     */
    public function setRouterParams(array $routerParams): void
    {
        $this->routerParams = $routerParams;
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
