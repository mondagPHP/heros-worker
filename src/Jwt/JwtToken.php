<?php

declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 */

namespace Framework\Jwt;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Framework\Jwt\Exception\JwtCacheTokenException;
use Framework\Jwt\Exception\JwtConfigException;
use Framework\Jwt\Exception\JwtTokenException;
use Framework\Jwt\Exception\JwtTokenExpiredException;
use UnexpectedValueException;

class JwtToken
{
    /**
     * @desc: 获取当前登录ID
     *
     * @return mixed
     *
     * @throws JwtTokenException
     */
    public static function getCurrentId(): mixed
    {
        return self::getExtendVal('id') ?? '';
    }

    /**
     * @desc: 获取指定令牌扩展内容字段的值
     *
     * @param  string  $val
     * @return mixed
     */
    public static function getExtendVal(string $val): mixed
    {
        return self::getTokenExtend()[$val] ?? '';
    }

    /**
     * @desc 获取指定令牌扩展内容
     *
     * @return array
     *
     * @throws JwtTokenException
     */
    public static function getExtend(): array
    {
        return self::getTokenExtend();
    }

    /**
     * @desc: 生成令牌.
     *
     * @param  array  $extend
     * @return array
     *
     * @throws JwtConfigException
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public static function generateToken(array $extend): array
    {
        if (! isset($extend['id'])) {
            throw new JwtTokenException('缺少全局唯一字段：id');
        }
        $config = self::_getConfig();
        $config['access_exp'] = $extend['access_exp'] ?? $config['access_exp'];
        $payload = self::generatePayload($config, $extend);
        $secretKey = self::getPrivateKey($config);

        return [
            'token_type' => 'Bearer',
            'expires_in' => $config['access_exp'],
            'access_token' => self::makeToken($payload['accessPayload'], $secretKey, $config['algorithms']),
        ];
    }

    /**
     * @desc: 验证令牌
     *
     * @return array
     *
     * @throws JwtTokenException
     */
    public static function verify(): array
    {
        $token = self::getTokenFromHeaders();
        try {
            return self::verifyToken($token);
        } catch (SignatureInvalidException) {
            throw new JwtTokenException('身份验证令牌无效');
        } catch (BeforeValidException) {
            throw new JwtTokenException('身份验证令牌尚未生效');
        } catch (ExpiredException) {
            throw new JwtTokenExpiredException('身份验证会话已过期，请重新登录！');
        } catch (UnexpectedValueException) {
            throw new JwtTokenException('获取的扩展字段不存在');
        } catch (JwtCacheTokenException|\Exception $exception) {
            throw new JwtTokenException($exception->getMessage());
        }
    }

    /**
     * @desc: 获令牌有效期剩余时长.
     *
     * @return int
     */
    public static function getTokenExp(): int
    {
        return (int) self::verify()['exp'] - time();
    }

    /**
     * @desc: 获取扩展字段.
     *
     * @return array
     *
     * @throws JwtTokenException
     */
    private static function getTokenExtend(): array
    {
        return (array) self::verify()['extend'];
    }

    /**
     * @desc: 获取Header头部authorization令牌
     *
     * @throws JwtTokenException
     */
    private static function getTokenFromHeaders(): string
    {
        $authorization = request()->header('authorization');
        if (! $authorization || 'undefined' == $authorization) {
            throw new JwtTokenException('请求未携带authorization信息');
        }
        if (2 != count(explode(' ', $authorization))) {
            throw new JwtTokenException('Bearer验证中的凭证格式有误，中间必须有个空格');
        }
        [$type, $token] = explode(' ', $authorization);
        if ('Bearer' !== $type) {
            throw new JwtTokenException('接口认证方式需为Bearer');
        }
        if (! $token || 'undefined' === $token) {
            throw new JwtTokenException('尝试获取的Authorization信息不存在');
        }

        return $token;
    }

    /**
     * @desc: 校验令牌
     *
     * @param  string  $token
     * @return array
     */
    private static function verifyToken(string $token): array
    {
        $config = self::_getConfig();
        $publicKey = self::getPublicKey($config['algorithms']);
        JWT::$leeway = $config['leeway'];
        $decoded = JWT::decode($token, new Key($publicKey, $config['algorithms']));
        $token = json_decode(json_encode($decoded), true);
        if ($config['is_single_device']) {
            RedisHandler::verifyToken($config['cache_token_pre'], (string) $token['extend']['id'], request()->getRealIp());
        }

        return $token;
    }

    /**
     * @desc: 生成令牌.
     *
     * @param  array  $payload 载荷信息
     * @param  string  $secretKey 签名key
     * @param  string  $algorithms 算法
     * @return string
     */
    private static function makeToken(array $payload, string $secretKey, string $algorithms): string
    {
        return JWT::encode($payload, $secretKey, $algorithms);
    }

    /**
     * @desc: 获取加密载体.
     *
     * @param  array  $config 配置文件
     * @param  array  $extend 扩展加密字段
     * @return array
     */
    private static function generatePayload(array $config, array $extend): array
    {
        if ($config['is_single_device']) {
            RedisHandler::generateToken([
                'id' => $extend['id'],
                'ip' => request()->getRealIp(),
                'extend' => json_encode($extend),
                'cache_token_ttl' => $config['cache_token_ttl'],
                'cache_token_pre' => $config['cache_token_pre'],
            ]);
        }
        $basePayload = [
            'iss' => $config['iss'],
            'iat' => time(),
            'exp' => time() + $config['access_exp'],
            'extend' => $extend,
        ];
        $resPayLoad['accessPayload'] = $basePayload;

        return $resPayLoad;
    }

    /**
     * @desc: 根据签名算法获取【公钥】签名值
     *
     * @param  string  $algorithm 算法
     * @return string
     *
     * @throws JwtConfigException
     */
    private static function getPublicKey(string $algorithm): string
    {
        $config = self::_getConfig();

        return match ($algorithm) {
            'RS512', 'RS256' => $config['access_public_key'],
            default => $config['access_secret_key'],
        };
    }

    /**
     * @desc: 根据签名算法获取【私钥】签名值
     *
     * @param  array  $config 配置文件
     * @return string
     */
    private static function getPrivateKey(array $config): string
    {
        return match ($config['algorithms']) {
            'RS512', 'RS256' => $config['access_private_key'],
            default => $config['access_secret_key'],
        };
    }

    /**
     * @desc: 获取配置文件
     *
     * @return array
     *
     * @throws JwtConfigException
     */
    private static function _getConfig(): array
    {
        $config = config('jwt');
        if (empty($config)) {
            throw new JwtConfigException('jwt配置文件不存在');
        }

        return $config;
    }
}
