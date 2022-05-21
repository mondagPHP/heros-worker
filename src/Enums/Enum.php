<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Enums;

use Framework\Traits\InstanceTrait;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;

/**
 * @method static getMessage($code)
 */
abstract class Enum
{
    use InstanceTrait;

    protected AdapterInterface $_adapter;

    #[Pure] public function __construct()
    {
        $this->_adapter = new ReflectionAdapter(static::class);
    }

    /**
     * @param $name
     * @param $arguments
     * @return string
     */
    public function __call($name, $arguments)
    {
        if (!Str::startsWith($name, 'get')) {
            throw new EnumException('The function is not defined!');
        }
        if (!isset($arguments) || count($arguments) === 0) {
            throw new EnumException('The Code is required');
        }
        $code = $arguments[0];
        $name = strtolower(substr($name, 3));
        if (isset($this->$name)) {
            return $this->$name[$code] ?? '';
        }
        $ref = new \ReflectionClass(static::class);
        $properties = $ref->getDefaultProperties();
        $arr = $this->_adapter->getAnnotationsByName($name, $properties);
        $this->$name = $arr;
        return $this->$name[$code] ?? '';
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
}
