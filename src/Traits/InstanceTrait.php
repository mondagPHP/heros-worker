<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Traits;

trait InstanceTrait
{
    protected static array $_instances = [];

    protected string $instanceKey;

    public static function getInstance(?string $key = null, bool $refresh = false)
    {
        if (! isset($key)) {
            $key = static::class;
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
     *
     * @author limx
     */
    public function flushInstance(): void
    {
        unset(static::$_instances[$this->instanceKey]);
    }
}
