<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\server;

use Workerman\Connection\TcpConnection;
use Workerman\Worker;

/**
 * Class GlobalDataServer.
 */
class GlobalDataServer
{
    /**
     * Worker instance.
     * @var worker
     */
    protected $_worker;

    /**
     * All data.
     * @var array
     */
    protected $_dataArray = [];

    /**
     * Construct.
     * @param string $ip
     * @param int    $port
     */
    public function __construct($ip = '0.0.0.0', $port = 2207)
    {
        $worker = new Worker("frame://{$ip}:{$port}");
        $worker->count = 1;
        $worker->name = 'globalData';
        $worker->onMessage = [$this, 'onMessage'];
        $worker->reloadable = false;
        $this->_worker = $worker;
    }

    /**
     * onMessage.
     * @return null|bool|void
     */
    public function onMessage(TcpConnection $connection, string $buffer)
    {
        if ('ping' === $buffer) {
            return;
        }
        $data = unserialize($buffer);
        if (! $buffer || ! isset($data['cmd']) || ! isset($data['key'])) {
            $connection->close(serialize('bad request'));
            return;
        }
        $cmd = $data['cmd'];
        $key = $data['key'];
        switch ($cmd) {
            case 'get':
                if (! isset($this->_dataArray[$key])) {
                    return $connection->send('N;');
                }
                return $connection->send(serialize($this->_dataArray[$key]));
                break;
            case 'set':
                $this->_dataArray[$key] = $data['value'];
                $connection->send('b:1;');
                break;
            case 'add':
                if (isset($this->_dataArray[$key])) {
                    return $connection->send('b:0;');
                }
                $this->_dataArray[$key] = $data['value'];
                return $connection->send('b:1;');
                break;
            case 'increment':
                if (! isset($this->_dataArray[$key])) {
                    return $connection->send('b:0;');
                }
                if (! is_numeric($this->_dataArray[$key])) {
                    $this->_dataArray[$key] = 0;
                }
                $this->_dataArray[$key] = $this->_dataArray[$key] + $data['step'];
                return $connection->send(serialize($this->_dataArray[$key]));
                break;
            case 'cas':
                $old_value = ! isset($this->_dataArray[$key]) ? null : $this->_dataArray[$key];
                if (md5(serialize($old_value)) === $data['md5']) {
                    $this->_dataArray[$key] = $data['value'];
                    return $connection->send('b:1;');
                }
                $connection->send('b:0;');
                break;
            case 'delete':
                unset($this->_dataArray[$key]);
                $connection->send('b:1;');
                break;
            default:
                $connection->close(serialize('bad cmd ' . $cmd));
        }
    }
}
