<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\yapi;

use FastRoute\RouteParser\Std;
use framework\annotations\RequestMapping;
use framework\vo\RequestVoInterface;
use ReflectionMethod;

class Method
{
    private $uri = '';

    private $method;

    private $queryParams = [];

    private $uriParams = [];

    private $msg;

    /**
     * Method constructor.
     * @param ReflectionMethod $method
     * @param RequestMapping $mapping
     * @throws \ReflectionException
     */
    public function __construct(ReflectionMethod $method, RequestMapping $mapping)
    {
        $this->method = count($mapping->method) > 0 ? current($mapping->method) : 'GET';
        $this->method = strtoupper($this->method);
        $this->msg = $mapping->msg === '' ? $method->getName() . ' 方法' : $mapping->msg;
        $this->parseParams($method, $mapping->value);
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
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @return array
     */
    public function getQueryParamsJson(): array
    {
        $return = [];
        foreach ($this->queryParams ?? [] as $param) {
            $return[] = [
                'name' => $param,
                'type' => 'text',
            ];
        }
        return $return;
    }

    /**
     * @param array $queryParams
     */
    public function setQueryParams(array $queryParams): void
    {
        $this->queryParams = $queryParams;
    }

    /**
     * @return array
     */
    public function getUriParams(): array
    {
        return $this->uriParams;
    }

    /**
     * @return array
     */
    public function getUriParamsJson(): array
    {
        $return = [];
        foreach ($this->uriParams ?? [] as $param) {
            $return[] = [
                'name' => $param,
                'type' => 'text',
            ];
        }
        return $return;
    }

    /**
     * @param array $uriParams
     */
    public function setUriParams(array $uriParams): void
    {
        $this->uriParams = $uriParams;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * @param string $msg
     */
    public function setMsg(string $msg): void
    {
        $this->msg = $msg;
    }

    /**
     * @param ReflectionMethod $method
     * @param string $uri
     * @throws \ReflectionException
     */
    private function parseParams(ReflectionMethod $method, string $uri): void
    {
        $uriParams = $this->parseUri($uri);
        $params = $method->getParameters();
        foreach ($params ?? [] as $parameter) {
            if (isset($uriParams[$parameter->getName()])) {
                //跳过route参数
                continue;
            }
            if ($parameter->getClass() === null) {
                $this->queryParams[] = $parameter->getName();
                continue;
            }
            if ($parameter->getClass() !== null && stripos($parameter->getClass(), RequestVoInterface::class) !== false) {
                $instance = new \ReflectionClass($parameter->getClass()->getName());
                if ($instance->newInstanceArgs([]) instanceof RequestVoInterface) {
                    $properties = $this->getProperties($instance);
                    foreach ($properties ?? [] as $property) {
                        $this->queryParams[] = $property;
                    }
                    continue;
                }
            }
        }
        $this->uriParams = $uriParams;
    }

    /**
     * @param string $uri
     * @return array
     */
    private function parseUri(string $uri): array
    {
        $variables = [];
        $routeDatas = (new Std())->parse($uri);
        foreach ($routeDatas ?? [] as $routeData) {
            foreach ($routeData as $part) {
                if (is_string($part)) {
                    $this->uri .= $part;
                    continue;
                }
                [$varName, ] = $part;
                $this->uri .= '{' . $varName . '}';

                $variables[$varName] = $varName;
            }
        }
        return $variables;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return array
     */
    private function getProperties(\ReflectionClass $reflectionClass): array
    {
        $params = [];
        foreach ($reflectionClass->getProperties() ?? [] as $property) {
            $params[] = $property->getName();
        }
        if ($reflectionClass->getParentClass()) {
            $params = array_merge($params, $this->getProperties($reflectionClass->getParentClass()));
        }
        return $params;
    }
}
