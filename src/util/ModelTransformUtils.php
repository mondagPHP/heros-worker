<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\util;

use framework\exception\HeroException;
use framework\string\StringUtils;

/**
 * Class ModelTransformUtils.
 */
class ModelTransformUtils
{
    /**
     * map转换为数据模型.
     * @throws HeroException
     * @throws \ReflectionException
     */
    public static function map2Model(string $class, array $map = []): object
    {
        $refClass = new \ReflectionClass($class);
        $obj = $refClass->newInstance();
        foreach ($map as $key => $value) {
            $methodName = 'set' . ucwords(StringUtils::underline2hump($key));
            if ($refClass->hasMethod($methodName)) {
                $method = $refClass->getMethod($methodName);
                $method->invoke($obj, $value);
            }
        }
        return $obj;
    }

    /**
     * 模型对象转为map.
     * @param $model
     * @throws \ReflectionException
     */
    public static function model2Map($model): array
    {
        $refClass = new \ReflectionClass($model);
        $properties = $refClass->getProperties();
        $map = [];
        foreach ($properties as $value) {
            $property = $value->getName();
            if (strpos($property, '_')) {
                $property = StringUtils::underline2hump($property); //转换成驼锋格式
            }
            $method = 'get' . ucfirst($property);
            $map[$property] = $model->{$method}();
        }
        return $map;
    }
}
