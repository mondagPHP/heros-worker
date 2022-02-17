<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace Framework\Session;

class RedisClusterSessionHandler extends RedisSessionHandler
{
    /**
     * @throws \RedisClusterException
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->_maxLifeTime = (int)ini_get('session.gc_maxlifetime');
        $timeout = $config['timeout'] ?? 2;
        $read_timeout = $config['read_timeout'] ?? $timeout;
        $persistent = $config['persistent'] ?? false;
        $auth = $config['auth'] ?? '';
        $args = [null, $config['host'], $timeout, $read_timeout, $persistent];
        if ($auth) {
            $args[] = $auth;
        }
        $this->_redis = new \RedisCluster(...$args);
        if (empty($config['prefix'])) {
            $config['prefix'] = 'redis_session_';
        }
        $this->_redis->setOption(\Redis::OPT_PREFIX, $config['prefix']);
    }
}
