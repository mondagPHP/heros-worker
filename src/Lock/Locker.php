<?php

declare(strict_types=1);

namespace Framework\Lock;

use Framework\Redis\Redis;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\RedisStore;

/**
 * @method static LockInterface lock(string $key, ?float $ttl = null, ?bool $autoRelease = null, ?string $prefix = null)
 */
class Locker
{
    protected static LockFactory $factory;

    public static function __callStatic($name, $arguments)
    {
        return static::createLock(...$arguments);
    }

    /**
     * 创建锁
     *
     * @param  string  $key
     * @param  float|null  $ttl 锁超时时间
     * @param  bool|null  $autoRelease 是否自动释放锁
     * @param  string|null  $prefix 锁前缀
     * @return LockInterface
     */
    protected static function createLock(string $key, ?float $ttl = null, ?bool $autoRelease = null, ?string $prefix = null): LockInterface
    {
        $config = config('lock.default_config', []);
        $ttl = $ttl !== null ? $ttl : ($config['ttl'] ?? 300);
        $autoRelease = $autoRelease !== null ? $autoRelease : ($config['auto_release'] ?? true);
        $prefix = $prefix !== null ? $prefix : ($config['prefix'] ?? 'lock_');

        return static::getLockFactory()->createLock($prefix.$key, $ttl, $autoRelease);
    }

    /**
     * @return LockFactory
     */
    protected static function getLockFactory(): LockFactory
    {
        if (! isset(static::$factory)) {
            $storage = config('lock.storage');
            if ($storage === 'file') {
                $lockPath = runtime_path().DIRECTORY_SEPARATOR.'lock';
                $storageInstance = new FlockStore($lockPath);
            } elseif ($storage === 'redis') {
                $redis = Redis::connection('default')->client();
                $storageInstance = new RedisStore($redis);
            } else {
                throw new \RuntimeException('lock driver not support!');
            }
            static::$factory = new LockFactory($storageInstance);
        }

        return static::$factory;
    }
}
