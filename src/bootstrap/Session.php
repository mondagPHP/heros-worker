<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\bootstrap;

use framework\core\Bootstrap;
use Workerman\Protocols\Http;
use Workerman\Protocols\Http\Session as SessionBase;
use Workerman\Worker;

/**
 * Class Session.
 */
class Session implements Bootstrap
{
    public static function start(Worker $worker)
    {
        $config = config('session');
        if (isset($config['gc_maxlifetime'])) {
            ini_set('session.gc_maxlifetime', (int)$config['gc_maxlifetime']);
        }
        if (isset($config['enable']) && $config['enable']) {
            Http::sessionName($config['session_name']);
            SessionBase::handlerClass($config['handler'], $config['config'][$config['handler']]);
        }
    }
}
