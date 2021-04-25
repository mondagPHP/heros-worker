<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\http;

use Workerman\Protocols\Http\Session as WorkerSession;

/**
 * Class Session.
 */
class Session
{
    private $session;

    public function __construct(WorkerSession $session)
    {
        $this->session = $session;
    }

    /**
     * @return static
     */
    public static function init(WorkerSession $session): self
    {
        return new self($session);
    }

    public function all(): array
    {
        return $this->session->all();
    }

    /**
     * @param $name
     * @param  null       $default
     * @return null|mixed
     */
    public function get($name, $default = null)
    {
        return $this->session->get($name, $default);
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->session->set($name, $value);
    }

    /**
     * @param $key
     * @param null $value
     */
    public function put($key, $value = null)
    {
        $this->session->put($key, $value);
    }

    public function forget($name)
    {
        $this->session->forget($name);
    }

    public function delete($name)
    {
        $this->forget($name);
    }

    public function pull($name, $default = null)
    {
        $this->session->pull($name, $default);
    }

    public function flush()
    {
        $this->session->flush();
    }

    public function clear()
    {
        $this->flush();
    }

    public function has($name): bool
    {
        return $this->session->has($name);
    }

    public function exists($name): bool
    {
        return $this->session->exists($name);
    }

    public function getWorkerSession(): WorkerSession
    {
        return $this->session;
    }
}
