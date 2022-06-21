<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
use Framework\Annotation\Middlewares;
use Framework\Annotation\RequestMapping;
use Framework\Annotation\Valid;
use Framework\Annotation\VO;
use Framework\Casbin\AuthManager;
use Framework\Component\MiddleWareCollector;
use Framework\Component\RouterCollector;
use Framework\Core\Container;
use Framework\Exception\ClassNotFoundException;
use Framework\Http\HttpRequest;
use Framework\Util\PipeLine;
use Framework\Validate\Validate;
use Monda\Utils\Util\ModelTransformUtil;

return [
    RequestMapping::class => static function (ReflectionMethod $method, mixed $instance, ReflectionAttribute $self) {
        /** @var RequestMapping $requestMapping */
        $requestMapping = $self->newInstance();
        $path = $requestMapping->path;
        if ('' === $path) {
            return $instance;
        }
        if (! str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        $requestMethods = $requestMapping->method;
        foreach ($requestMethods as &$itemRequestMethod) {
            $itemRequestMethod = strtoupper($itemRequestMethod);
        }

        $slug = $requestMapping->slug;

        /** @var RouterCollector $routerCollector */
        $routerCollector = container(RouterCollector::class);
        $routerDispatch = static function (HttpRequest $request) use ($method, $instance, $slug) {

            //验证用户slug权限
            AuthManager::checkAuth($slug);

            $params = $request->getParams();
            $request->pushInjectObject($request);
            $extParams = array_values($request->getInjectObject());
            //_initialize 初始化
            if (method_exists($instance, '_initialize')) {
                call_user_func([$instance, '_initialize'], $request);
            }
            //验证器Vo
            $validAttributes = $method->getAttributes(Valid::class);
            foreach ($validAttributes  as $validAttribute) {
                /** @var Valid $methodValidInstance */
                $methodValidInstance = $validAttribute->newInstance();
                $methodVInstance = new $methodValidInstance->class;
                if (! $methodVInstance instanceof Validate) {
                    continue;
                }
                $methodVInstance->valid($methodValidInstance->scene);
            }
            $inputParams = [];
            $reflectionParameters = $method->getParameters();
            foreach ($reflectionParameters  as $reflectionParameter) {
                if (isset($params[$reflectionParameter->getName()])) {
                    $inputParams[] = $params[$reflectionParameter->getName()];
                } else {
                    if ($reflectionParameter->getType()?->isBuiltin() || $reflectionParameter->getType()?->isBuiltin() === null) {
                        if ($reflectionParameter->getType()->allowsNull()) {
                            $inputParams[] = null;
                        } else {
                            $inputParams[] = false;
                        }
                    } else {
                        $parameterClass = $reflectionParameter->getType()->getName();
                        if (! class_exists($parameterClass)) {
                            throw new ClassNotFoundException("{$parameterClass} not found!");
                        }
                        $reflectionClass = new ReflectionClass($parameterClass);
                        $isVO = searchForTopParent($reflectionClass, VO::class);
                        if ($isVO) {
                            $vo = ModelTransformUtil::map2Model($parameterClass, $params);
                            $inputParams[] = $vo;
                        } else {
                            $extFun = function () use ($reflectionClass, $extParams) {
                                foreach ($extParams  as $extParam) {
                                    if (null !== $reflectionClass && $reflectionClass->isInstance($extParam)) {
                                        return $extParam;
                                    }
                                }
                                if (class_exists($reflectionClass->getName()) && Container::has($reflectionClass->getName())) {
                                    return Container::get($reflectionClass->getName());
                                }
                                return false;
                            };
                            $inputParams[] = $extFun();
                        }
                    }
                }
            }
            $return = $method->invokeArgs($instance, $inputParams);
            if (method_exists($instance, '_finish')) {
                call_user_func([$instance, '_finish'], $request);
            }
            return $return;
        };
        /** @var MiddleWareCollector $middlewareCollector */
        $middlewareCollector = container(MiddleWareCollector::class);
        $middlewares = $middlewareCollector->get($path);
        if (property_exists($instance, 'middlewares') && $instance->middlewares) {
            $middlewares = array_merge($middlewares, $instance->middlewares);
        }
        //注解在控制器上
        $clazzAttributes = (new ReflectionClass(get_class($instance)))->getAttributes(Middlewares::class);
        foreach ($clazzAttributes as $clazzAttribute) {
            foreach ($clazzAttribute->getArguments()  as $annotationMiddlewares) {
                $middlewares = array_merge($middlewares, $annotationMiddlewares);
            }
        }
        $routerDispatch = container(PipeLine::class)->create()->setClasses($middlewares)->run($routerDispatch);
        foreach ($requestMethods as $requestMethod) {
            $routerCollector->addRouter($requestMethod, $path, $routerDispatch);
        }
        return $instance;
    },
];
