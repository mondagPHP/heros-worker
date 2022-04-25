<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework;

use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Class App.
 */
class App
{
    private static $annotationHandlers = [];

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public static function init()
    {
        self::$annotationHandlers = self::initAnnotationHandlers();
        //为啥扫描
        $scans = [
            __DIR__ . '/boot' => 'framework\\',
            config('app.scan_dir') => config('app.scan_root_namespace'),
        ];
        foreach ($scans ?? [] as $scanDir => $scanRootNamespace) {
            self::scanBeans($scanDir, $scanRootNamespace); //扫描
        }
    }

    /**
     * @param $dir
     */
    private static function getAllBeansFiles($dir): array
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
     * @param $scanDir
     * @param $scanRootNamespace
     * @throws ReflectionException
     */
    private static function scanBeans($scanDir, $scanRootNamespace)
    {
        $files = self::getAllBeansFiles($scanDir);
        foreach ($files ?? [] as $file) {
            require_once $file;
        }
        $reader = new AnnotationReader();
        foreach (get_declared_classes() ?? [] as $class) {
            if (strstr($class, $scanRootNamespace)) {
                $refClass = new ReflectionClass($class);
                //类注解
                $classAnnotations = $reader->getClassAnnotations($refClass);
                foreach ($classAnnotations ?? [] as $classAnnotation) {
                    //没有注解处理器不做任何处理
                    if (! isset(self::$annotationHandlers[get_class($classAnnotation)])) {
                        continue;
                    }
                    $handler = self::$annotationHandlers[get_class($classAnnotation)];
                    $instance = container()->get($class);
                    self::handlerPropertyAnnotations($instance, $refClass, $reader);
                    self::handlerMethodAnnotations($instance, $refClass, $reader);
                    $handler($instance, $classAnnotation);
                }
            }
        }
    }

    /**
     * 处理属性.
     * @param $instance
     * @param \ReflectionClass $reflectionClass
     * @param \Doctrine\Common\Annotations\AnnotationReader $reader
     */
    private static function handlerPropertyAnnotations(&$instance, ReflectionClass $reflectionClass, AnnotationReader $reader)
    {
        $properties = $reflectionClass->getProperties();
        foreach ($properties ?? [] as $property) {
            $propAnnotations = $reader->getPropertyAnnotations($property);
            foreach ($propAnnotations ?? [] as $propAnnotation) {
                if (! isset(self::$annotationHandlers[get_class($propAnnotation)])) {
                    continue;
                }
                $handler = self::$annotationHandlers[get_class($propAnnotation)];
                $handler($property, $instance, $propAnnotation);
            }
        }
    }

    /**
     * 处理方法.
     * @param $instance
     * @param \ReflectionClass $reflectionClass
     * @param \Doctrine\Common\Annotations\AnnotationReader $reader
     */
    private static function handlerMethodAnnotations(&$instance, ReflectionClass $reflectionClass, AnnotationReader $reader)
    {
        $methods = $reflectionClass->getMethods();
        foreach ($methods ?? [] as $method) {
            $methodAnnotations = $reader->getMethodAnnotations($method);
            foreach ($methodAnnotations ?? [] as $methodAnnotation) {
                if (! isset(self::$annotationHandlers[get_class($methodAnnotation)])) {
                    continue;
                }
                $handler = self::$annotationHandlers[get_class($methodAnnotation)];
                $handler($method, $instance, $methodAnnotation);
            }
        }
    }

    /**
     * 初始化.
     */
    private static function initAnnotationHandlers(): array
    {
        $handlers = [];
        $annotationHandlerFiles = glob(__DIR__ . '/annotations/handler/*.php');
        foreach ($annotationHandlerFiles ?? [] as $annotationHandlerFile) {
            $handlers = array_merge($handlers, (array) require_once $annotationHandlerFile);
        }
        return $handlers;
    }
}
