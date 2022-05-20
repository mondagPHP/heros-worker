<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Traits;

trait InstanceTrait
{
    protected static $_instances = [];

    protected $instanceKey;

    public static function getInstance($key = null, $refresh = false)
    {
        if (! isset($key)) {
            $key = get_called_class();
        }

        if (! $refresh && isset(static::$_instances[$key]) && static::$_instances[$key] instanceof static) {
            return static::$_instances[$key];
        }

        $client = new static();
        $client->instanceKey = $key;
        return static::$_instances[$key] = $client;
    }

    /**
     * @desc   回收单例对象
     * @author limx
     */
    public function flushInstance()
    {
        unset(static::$_instances[$this->instanceKey]);
    }
}
