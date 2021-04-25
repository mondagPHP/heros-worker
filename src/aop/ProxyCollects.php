<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\aop;

/**
 * Class ProxyCollects.
 */
class ProxyCollects
{
    /** @var array */
    private $classesMap = [];

    /** @var array */
    private $classMethodMap = [];

    private $proxyClasses = [];

    public function addClassMap(string $className, string $aspectName, array $methods, string $filePath): void
    {
        if (empty($filePath)) {
            return;
        }
        if (! isset($this->classesMap[$className])) {
            $this->classesMap[$className] = [
                'aspects' => [],
                'methods' => [],
                'filePath' => $filePath,
            ];
        }
        $this->classesMap[$className]['aspects'][] = $aspectName;
        $this->classesMap[$className]['methods'] = array_merge($this->classesMap[$className]['methods'], $methods);
        if (! isset($this->classMethodMap[$className])) {
            $this->classMethodMap[$className] = [];
        }
        foreach ($methods as $method) {
            if (! isset($this->classMethodMap[$className][$method])) {
                $this->classMethodMap[$className][$method] = [];
            }
            $this->classMethodMap[$className][$method][] = $aspectName;
        }
    }

    public function setNewPath(string $className, string $filePath): void
    {
        $this->classesMap[$className]['newPath'] = $filePath;
    }

    public function setProxyClassName(string $className, string $proxyClassName, string $path): void
    {
        $this->classesMap[$className]['proxyClassName'] = $proxyClassName;
        $this->proxyClasses[$proxyClassName] = [$path, $className];
    }

    public function getProxyClasses(): array
    {
        return $this->proxyClasses;
    }

    /**
     * @param $className
     */
    public function shouldAllRewrite($className): bool
    {
        return in_array('*', $this->classesMap[$className]['methods'] ?? [], true);
    }

    public function shouldRewrite(string $className, string $method): bool
    {
        if ($this->shouldAllRewrite($className)) {
            return true;
        }
        return in_array($method, $this->classesMap[$className]['methods'] ?? [], true);
    }

    public function shouldUseTrait(string $className): bool
    {
        return isset($this->classesMap[$className]);
    }

    public function getClassMap(): array
    {
        return $this->classesMap;
    }
}
