<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Bootstrap;

use Framework\Contract\BootstrapInterface;
use Workerman\Protocols\Http;
use Workerman\Protocols\Http\Session as SessionBase;
use Workerman\Worker;

/**
 * Class Session
 * @package Framework\Bootstrap
 */
class Session implements BootstrapInterface
{
    public static function start(Worker $worker): void
    {
        $config = config('session', []);
        if (isset($config['enable']) && $config['enable']) {
            Http::sessionName($config['session_name']);
            SessionBase::handlerClass($config['handler'], $config['config'][$config['handler']]);
        }
    }
}
