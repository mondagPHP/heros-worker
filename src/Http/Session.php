<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Http;

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
    private Request $request;

    private function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $name
     * @param $arguments
     * @return mixed
     */
    public function __call(string $name, $arguments)
    {
        return $this->request->session()->{$name}(...$arguments);
    }

    /**
     * @param Request $request
     * @return static
     */
    public static function init(Request $request): self
    {
        return new self($request);
    }
}
