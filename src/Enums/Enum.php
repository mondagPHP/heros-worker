<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Enums;

use Framework\Traits\InstanceTrait;

/**
 * @method static getMessage($code)
 * @method static array getMappings()
 */
abstract class Enum
{
    use InstanceTrait;

    protected AdapterInterface $_adapter;

    public function __construct()
    {
        $this->_adapter = new ReflectionAdapter(static::class);
    }

    /**
     * @param $name
     * @param $arguments
     * @return string
     * @throws \ReflectionException
     */
    public function __call($name, $arguments)
    {
        $method = $name . 'Call';
        if (! method_exists($this, $method)) {
            throw new \RuntimeException('method not exist!');
        }
        return $this->{$method}($arguments);
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return static::getInstance()->$method(...$arguments);
    }

    /**
     * @throws \ReflectionException
     */
    private function getMessageCall($arguments)
    {
        if (! isset($arguments) || count($arguments) === 0) {
            throw new EnumException('The Code is required');
        }
        $code = $arguments[0];
        $ref = new \ReflectionClass(static::class);
        $properties = $ref->getDefaultProperties();
        $arr = $this->_adapter->getAnnotationsByName($properties);
        return $arr[$code] ?? '';
    }

    /**
     * @param $arguments
     * @return array
     * @throws \ReflectionException
     */
    private function getMappingsCall($arguments): array
    {
        $ref = new \ReflectionClass(static::class);
        $properties = $ref->getDefaultProperties();
        return $this->_adapter->getAnnotationsByName($properties);
    }
}
