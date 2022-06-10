# CHANGELOG

## [v2.1.17] 2022.06.10

- Redis 支持 `phpredis`,`predis`不同驱动
- Session如下参数
```shell
            'auto_update_timestamp' => 'autoUpdateTimestamp',
            'cookie_lifetime' => 'cookieLifetime',
            'gc_probability' => 'gcProbability',
            'cookie_path' => 'cookiePath',
            'http_only' => 'httpOnly',
            'same_site' => 'sameSite',
            'lifetime' => 'lifetime',
            'domain' => 'domain',
            'secure' => 'secure',
```

## [v2.1.16]  2022.06.08
- 修复Vo的继承的问题。

## [v2.1.15] 2022.05.26

- Redis增加心跳,解决云Redis服务器自动断开链接的问题。