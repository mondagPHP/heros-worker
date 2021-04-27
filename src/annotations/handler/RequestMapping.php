<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */

use Doctrine\Common\Annotations\AnnotationReader;
use framework\annotations\Controller;
use framework\annotations\RequestMapping;
use framework\boot\MiddleWareCollector;
use framework\boot\RouterCollector;
use framework\core\PipeLine;
use framework\http\Request;
use framework\util\ModelTransformUtils;
use framework\vo\RequestVoInterface;

return [
    RequestMapping::class => function (ReflectionMethod $method, $instance, RequestMapping $self) {
        $path = $self->value;
        if ('' === $path) {
            return $instance;
        }
        if (strpos($path, '/') !== 0 ) {
            $path = '/' . $path;
        }
        $classAnnotation = (new AnnotationReader())->getClassAnnotation($method->getDeclaringClass(), Controller::class);
        if (($classAnnotation instanceof Controller) && !empty(trim(trim($classAnnotation->routePrefix), '/'))) {
            $path = '/' . trim($classAnnotation->routePrefix, '/') . $path;
        }
        $requestMethod = count($self->method) > 0 ? $self->method : ['GET'];
        /**
         * @var RouterCollector $routerCollector
         */
        $routerCollector = container()->get(RouterCollector::class);
        //$params uri参数
        //$extParams
        $routerDispatch = static function (Request $request, array $params, array $extParams = []) use ($method, $instance) {
            $inputParams = [];
            $reflectionParameters = $method->getParameters();
            /** @var ReflectionParameter $reflectionParameter */
            foreach ($reflectionParameters ?? [] as $reflectionParameter) {
                $parameterClass = $reflectionParameter->getClass();
                //Route.php
                if (isset($params[$reflectionParameter->getName()])) {
                    $inputParams[] = $params[$reflectionParameter->getName()];
                } else {
                    //注入对象
                    if (null === $parameterClass) {
                        $inputParams[] = false;
                    } else {
                        if ($parameterClass->implementsInterface(RequestVoInterface::class)) {
                            $vo = ModelTransformUtils::map2Model($parameterClass->getName(), $request->getParams());
                            //vo验证
                            if (container()->has($parameterClass->getName())) {
                                container()->get($parameterClass->getName())($vo);
                            }
                            $inputParams[] = $vo;
                        } else {
                            //static function
                            $extFun = static function () use ($parameterClass, $extParams) {
                                foreach ($extParams ?? [] as $extParam) {
                                    if (null !== $parameterClass && $parameterClass->isInstance($extParam)) {
                                        return $extParam;
                                    }
                                }
                                return false;
                            };
                            $inputParams[] = $extFun();
                        }
                    }
                }
            }
            return $method->invokeArgs($instance, $inputParams);
        };
        /** @var MiddleWareCollector $middlewareCollector */
        $middlewareCollector = container()->get(MiddleWareCollector::class);
        $middlewares = $middlewareCollector->get($path);
        $routerDispatch = container()->get(PipeLine::class)->create()->setClasses($middlewares)->run($routerDispatch);
        $routerCollector->addRouter($requestMethod, $path, $routerDispatch);
        return $instance;
    },
];
