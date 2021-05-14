<?php


namespace framework\yapi;


use Doctrine\Common\Annotations\AnnotationReader;
use framework\annotations\Controller;
use framework\annotations\RequestMapping;
use ReflectionClass;

class Collector
{
    protected $scanPath;

    private $controllerMap;

    private $methodMap;

    /**
     * Collector constructor.
     * @param array $path
     */
    public function __construct(array $path)
    {
        $this->scanPath = $path;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function collector(): array
    {
        foreach ($this->scanPath ?? [] as $scanDir => $scanRootNamespace) {
            $this->scanController($scanDir, $scanRootNamespace);
        }
        return [$this->controllerMap, $this->methodMap];
    }

    /**
     * @param string $dir
     * @param string $scanRootNamespace
     * @throws \ReflectionException
     */
    private function scanController(string $dir, string $scanRootNamespace): void
    {
        $files = $this->getAllBeansFiles($dir);
        foreach ($files ?? [] as $file) {
            require_once $file;
        }
        $reader = new AnnotationReader();
        foreach (get_declared_classes() ?? [] as $class) {
            if (strpos($class, $scanRootNamespace) !== false) {
                $refClass = new ReflectionClass($class);
                //类注解
                /** @var Controller $classAnnotation */
                $classAnnotation = $reader->getClassAnnotation($refClass, Controller::class);
                if (! $classAnnotation) {
                    continue;
                }
                $controllerName = $classAnnotation->msg;
                if ($controllerName === '') {
                    $controllerName = $refClass->getShortName() . ' 控制器';
                }
                $this->controllerMap[$class] = $controllerName;
                $this->parseMethod($refClass, $reader);
            }
        }
    }

    /**
     * @param $dir
     * @return array
     */
    private function getAllBeansFiles($dir): array
    {
        $ret = [];
        $files = glob($dir . '/*');
        foreach ($files ?? [] as $file) {
            if (is_dir($file)) {
                $ret = array_merge($ret, self::getAllBeansFiles($file));
            } elseif ('php' === pathinfo($file)['extension']) {
                $ret[] = $file;
            }
        }
        return $ret;
    }

    /**
     * @param ReflectionClass $refClass
     * @param AnnotationReader $reader
     * @throws \ReflectionException
     */
    private function parseMethod(ReflectionClass $refClass, AnnotationReader $reader): void
    {
        $methods = $refClass->getMethods();
        foreach ($methods ?? [] as $method) {
            /** @var RequestMapping $methodAnnotation */
            $methodAnnotation = $reader->getMethodAnnotation($method, RequestMapping::class);
            if (! $methodAnnotation) {
                continue;
            }
            $this->methodMap[$refClass->getName()][] = new Method($method, $methodAnnotation);
        }
    }
}
