<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
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
    public static function start(?Worker $worker): void
    {
        $config = config('session');
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
