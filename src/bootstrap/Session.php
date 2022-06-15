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
        if (isset($config['enable']) && $config['enable']) {
            Http::sessionName($config['session_name']);
            SessionBase::handlerClass($config['handler'], $config['config'][$config['handler']]);
            $map = [
                'auto_update_timestamp' => 'autoUpdateTimestamp',
                'cookie_lifetime' => 'cookieLifetime',
                'gc_probability' => 'gcProbability',
                'cookie_path' => 'cookiePath',
                'http_only' => 'httpOnly',
                'same_site' => 'sameSite',
                'lifetime' => 'lifetime',
                'domain' => 'domain',
                'secure' => 'secure',
            ];
            foreach ($map as $key => $name) {
                if (isset($config[$key]) && property_exists(SessionBase::class, $name)) {
                    SessionBase::${$name} = $config[$key];
                }
            }
        }
    }
}
