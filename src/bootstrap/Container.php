<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\bootstrap;

use framework\core\Bootstrap;
use framework\exception\NotFoundException;
use Psr\Container\ContainerInterface;
use Workerman\Worker;

/**
 * @method static mixed get(string $id)
 * @method static bool has(string $id)
 * @method static void set(string $name, $instance)
 * @method static mixed make(string $name, array $constructor = [])
 *                                                                  Class Container
 */
class Container implements Bootstrap
{
    protected static $instance;

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::$instance->{$name}(...$arguments);
    }

    public static function start(Worker $worker = null)
    {
        if (static::$instance) {
            return;
        }
        static::$instance = new class() implements ContainerInterface {
            /**
             * @var array
             */
            protected $_instances = [];

            /**
             * @throws NotFoundException
             * @return mixed
             */
            public function get(string $id)
            {
                if (! isset($this->_instances[$id])) {
                    if (! class_exists($id)) {
                        throw new NotFoundException("Class '{$id}' not found");
                    }
                    $this->_instances[$id] = new $id();
                }
                return $this->_instances[$id];
            }

            public function has(string $id): bool
            {
                return \array_key_exists($id, $this->_instances);
            }

            /**
             * 容器设置.
             * @param $name
             * @param $instance
             */
            public function set($name, $instance): void
            {
                $this->_instances[$name] = $instance;
            }

            /**
             * @param $name
             * @throws NotFoundException
             * @return \framework\bootstrap\
             */
            public function make($name, array $constructor = [])
            {
                if (! class_exists($name)) {
                    throw new NotFoundException("Class '{$name}' not found");
                }
                return new $name(...array_values($constructor));
            }
        };
    }

    /**
     * 获取实例.
     * @return mixed
     */
    public static function instance()
    {
        return static::$instance;
    }
}
