<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Casbin;

use Casbin\Enforcer;
use Casbin\Exceptions\CasbinException;
use Framework\Exception\NoAuthException;

/**
 * Class AuthManager
 */
class AuthManager
{
    public const AUTH_ENABLE_KEY = 'casbin_auth_enable';

    public static string $defaultAct = 'defaultAct';

    protected static Enforcer $enforcer;

    protected static string $user = '';

    protected static bool $enable = false;

    /**
     * 获取Enforcer
     *
     * @throws CasbinException
     */
    public static function enforcer(): Enforcer
    {
        if (! isset(self::$enforcer)) {
            self::$enforcer = new Enforcer(__DIR__.'/config/rbac_model.conf', new DatabaseAdapter());
            self::$enable = config('app.'.self::AUTH_ENABLE_KEY, false);
        }

        return self::$enforcer;
    }

    /**
     * 在验权之前设置（比如中间件中）设置用户唯一标识，用于权限验证
     * 如果设置 user = 'root' 是超级管理员 就有所有权限
     *
     * @param  string  $user
     */
    public static function setUser(string $user): void
    {
        self::$user = $user;
    }

    /**
     * 检查权限
     *
     * @param  string  $slug
     * @param  string  $act
     *
     * @throws CasbinException
     */
    public static function checkAuth(string $slug, string $act = 'defaultAct'): void
    {
        if ($slug) {
            $enforcer = self::enforcer();
            if (self::$enable && $enforcer->enforce(self::$user, $slug, $act) === false) {
                throw new NoAuthException('no permission');
            }
        }
    }
}
