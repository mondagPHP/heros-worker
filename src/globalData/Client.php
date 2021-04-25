<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\globalData;

use Exception;
use Workerman\Lib\Timer;

/**
 * Class Client.
 * https://github.com/walkor/GlobalData.
 */
class Client
{
    /**
     * Timeout.
     * @var int
     */
    public $timeout = 5;

    /**
     * Heartbeat interval.
     * @var int
     */
    public $pingInterval = 25;

    /**
     * Global data server address.
     * @var array
     */
    protected $_globalServers = [];

    /**
     * Connection to global server.
     * @var resource
     */
    protected $_globalConnections;

    /**
     * Construct.
     * @param  array/string $servers
     * @throws Exception
     */
    public function __construct($servers)
    {
        if (empty($servers)) {
            throw new Exception('servers empty');
        }
        $this->_globalServers = array_values((array) $servers);
    }

    /**
     * Magic methods __set.
     * @param  mixed     $value
     * @throws Exception
     */
    public function __set(string $key, $value)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote([
            'cmd' => 'set',
            'key' => $key,
            'value' => $value,
        ], $connection);
        $this->readFromRemote($connection);
    }

    /**
     * Magic methods __isset.
     * @throws Exception
     */
    public function __isset(string $key)
    {
        return null !== $this->__get($key);
    }

    /**
     * Magic methods __unset.
     * @throws Exception
     */
    public function __unset(string $key)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote([
            'cmd' => 'delete',
            'key' => $key,
        ], $connection);
        $this->readFromRemote($connection);
    }

    /**
     * Magic methods __get.
     * @throws Exception
     */
    public function __get(string $key)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote([
            'cmd' => 'get',
            'key' => $key,
        ], $connection);
        return $this->readFromRemote($connection);
    }

    /**
     * Cas.
     * @param  mixed     $old_value
     * @param  mixed     $new_value
     * @throws Exception
     * @return mixed
     */
    public function cas(string $key, $old_value, $new_value)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote([
            'cmd' => 'cas',
            'md5' => md5(serialize($old_value)),
            'key' => $key,
            'value' => $new_value,
        ], $connection);
        return $this->readFromRemote($connection);
    }

    /**
     * Add.
     * @param  mixed     $value
     * @throws Exception
     */
    public function add(string $key, $value)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote([
            'cmd' => 'add',
            'key' => $key,
            'value' => $value,
        ], $connection);
        return $this->readFromRemote($connection);
    }

    /**
     * Increment.
     * @param  mixed     $step
     * @throws Exception
     */
    public function increment(string $key, $step = 1)
    {
        $connection = $this->getConnection($key);
        $this->writeToRemote([
            'cmd' => 'increment',
            'key' => $key,
            'step' => $step,
        ], $connection);
        return $this->readFromRemote($connection);
    }

    /**
     * Connect to global server.
     * @param  mixed     $key
     * @throws Exception
     */
    protected function getConnection($key)
    {
        $offset = crc32($key) % count($this->_globalServers);
        if ($offset < 0) {
            $offset = -$offset;
        }

        if (! isset($this->_globalConnections[$offset]) || ! is_resource($this->_globalConnections[$offset]) || feof($this->_globalConnections[$offset])) {
            $connection = stream_socket_client("tcp://{$this->_globalServers[$offset]}", $code, $msg, $this->timeout);
            if (! $connection) {
                throw new Exception($msg);
            }
            stream_set_timeout($connection, $this->timeout);
            if (class_exists('\Workerman\Lib\Timer') && 'cli' === php_sapi_name()) {
                $timer_id = Timer::add($this->pingInterval, function ($connection) use (&$timer_id) {
                    $buffer = pack('N', 8) . 'ping';
                    if (strlen($buffer) !== @fwrite($connection, $buffer)) {
                        @fclose($connection);
                        Timer::del($timer_id);
                    }
                }, [$connection]);
            }
            $this->_globalConnections[$offset] = $connection;
        }
        return $this->_globalConnections[$offset];
    }

    /**
     * Write data to global server.
     * @param  mixed     $data
     * @param  mixed     $connection
     * @throws Exception
     */
    protected function writeToRemote($data, $connection)
    {
        $buffer = serialize($data);
        $buffer = pack('N', 4 + strlen($buffer)) . $buffer;
        $len = fwrite($connection, $buffer);
        if ($len !== strlen($buffer)) {
            throw new Exception('writeToRemote fail');
        }
    }

    /**
     * Read data from global server.
     * @param  mixed     $connection
     * @throws Exception
     */
    protected function readFromRemote($connection)
    {
        $all_buffer = '';
        $total_len = 4;
        $head_read = false;
        while (1) {
            $buffer = fread($connection, 8192);
            if ('' === $buffer || false === $buffer) {
                throw new Exception('readFromRemote fail');
            }
            $all_buffer .= $buffer;
            $recv_len = strlen($all_buffer);
            if ($recv_len >= $total_len) {
                if ($head_read) {
                    break;
                }
                $unpack_data = unpack('Ntotal_length', $all_buffer);
                $total_len = $unpack_data['total_length'];
                if ($recv_len >= $total_len) {
                    break;
                }
                $head_read = true;
            }
        }
        return unserialize(substr($all_buffer, 4));
    }
}
