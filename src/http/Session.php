<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\http;

use Workerman\Protocols\Http\Request;

/**
 * Class Session.
 * @method array all()
 * @method mixed get(string $name, $default = null)
 * @method void set(string $name, $value)
 * @method void put(string $name, $value = null)
 * @method void pull(string $name, $value = null)
 * @method void forget(string $name)
 * @method void delete(string $name)
 * @method bool exists(string $name)
 * @method bool has(string $name)
 * @method void clear(string $name)
 * @method void flush()
 */
class Session
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->request->session()->{$name}(...$arguments);
    }

    /**
     * @return static
     */
    public static function init(Request $request): self
    {
        return new self($request);
    }
}
