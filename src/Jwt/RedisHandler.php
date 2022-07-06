<?php

declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 */

namespace Framework\Jwt;

use Framework\Jwt\Exception\JwtCacheTokenException;
use Framework\Redis\Redis;

class RedisHandler
{
    /**
     * @desc: 生成设备缓存令牌
     * （1）登录时，判断该账号是否在其它设备登录，如果有，就请空之前key清除，
     * （2）重新设置key 。然后存储用户信息和ip地址拼接为key，存储在redis当中
     *
     * @param  array  $args
     */
    public static function generateToken(array $args): void
    {
        $cacheKey = $args['cache_token_pre'].$args['id'];
        $key = Redis::keys($cacheKey.':*');
        if (! empty($key)) {
            Redis::del(current($key));
        }
        Redis::setex($cacheKey.':'.$args['ip'], $args['cache_token_ttl'], $args['extend']);
    }

    /**
     * @desc: 检查设备缓存令牌
     *
     * @param  string  $pre
     * @param  string  $uid
     * @param  string  $ip
     * @return bool
     */
    public static function verifyToken(string $pre, string $uid, string $ip): bool
    {
        $cacheKey = $pre.$uid.':'.$ip;
        if (! Redis::exists($cacheKey)) {
            throw new JwtCacheTokenException('该账号已在其他设备登录，强制下线');
        }

        return true;
    }
}
