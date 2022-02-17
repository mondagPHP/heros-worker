<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace Framework\Core;

/**
 * 扫描开始
 */
class Scanner
{
    /**
     * @var array
     */
    protected static array $annotationHandlers = [];

    /**
     * @throws \ReflectionException
     */
    public static function begin()
    {
        self::$annotationHandlers = self::initAnnotationHandlers();
        $scans = [
            dirname(__DIR__) . '/Component' => 'Framework\\Component',
            BASE_PATH . '/app' => 'App\\',
        ];
        foreach ($scans ?? [] as $scanDir => $scanRootNamespace) {
            self::scanBeans($scanDir, $scanRootNamespace); //扫描
        }
    }

    /**
     * 扫描PHP文件
     * @param string $dir
     * @return array
     */
    private static function getAllBeansFiles(string $dir): array
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
     * @param string $scanDir
     * @param string $scanRootNamespace
     * @throws \ReflectionException
     */
    private static function scanBeans(string $scanDir, string $scanRootNamespace)
    {
        $files = self::getAllBeansFiles($scanDir);
        foreach ($files ?? [] as $file) {
            require_once $file;
        }
        foreach (get_declared_classes() ?? [] as $class) {
            if (strstr($class, $scanRootNamespace)) {
                $refClass = new \ReflectionClass($class);
                $classAnnotations = $refClass->getAttributes();
                foreach ($classAnnotations ?? [] as $classAnnotation) {
                    if (! isset(self::$annotationHandlers[($classAnnotation->getName())])) {
                        continue;
                    }
                    $instance = Container::get($class);
                    self::handlerPropertyAnnotations($instance, $refClass);
                    self::handlerMethodAnnotations($instance, $refClass);
                }
            }
        }
    }

    /**
     * 初始化.
     */
    private static function initAnnotationHandlers(): array
    {
        $handlers = [];
        $annotationHandlerFiles = glob(dirname(__DIR__) . '/Annotation/Handler/*.php');
        foreach ($annotationHandlerFiles ?? [] as $annotationHandlerFile) {
            $handlers = array_merge($handlers, (array)require_once $annotationHandlerFile);
        }
        return $handlers;
    }

    /**
     * 收集属性
     * @param mixed $instance
     * @param \ReflectionClass $reflectionClass
     */
    private static function handlerPropertyAnnotations(mixed $instance, \ReflectionClass $reflectionClass): void
    {
        $properties = $reflectionClass->getProperties();
        foreach ($properties ?? [] as $property) {
            $propAnnotations = $property->getAttributes();
            foreach ($propAnnotations ?? [] as $propAnnotation) {
                if (! isset(self::$annotationHandlers[$propAnnotation->getName()])) {
                    continue;
                }
                $handler = self::$annotationHandlers[$propAnnotation->getName()];
                $handler($property, $instance, $propAnnotation);
            }
        }
    }

    /**
     * 收集方法
     * @param mixed $instance
     * @param \ReflectionClass $reflectionClass
     */
    private static function handlerMethodAnnotations(mixed $instance, \ReflectionClass $reflectionClass): void
    {
        $methods = $reflectionClass->getMethods();
        foreach ($methods ?? [] as $method) {
            $reflectionAttributes = $method->getAttributes();
            foreach ($reflectionAttributes ?? [] as $reflectionAttribute) {
                if (! isset(self::$annotationHandlers[$reflectionAttribute->getName()])) {
                    continue;
                }
                $handler = self::$annotationHandlers[$reflectionAttribute->getName()];
                $handler($method, $instance, $reflectionAttribute);
            }
        }
    }
}
