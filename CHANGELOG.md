# CHANGELOG
## [v2.1.27] 2022.07.18
- support herosworker in window
- add expectsJson() and expectJson() method in HttpRequest
- add ErrorCode by https://www.workerman.net/doc/webman/components/generate_error_code.html

## [v2.1.26] 2022.07.06
- 增加Locker
    ```php
  $lock = Locker::lock("hello", 200);
  if (!$lock->acquire()) {
     throw new \Exception('操作太频繁，请稍后再试');
  }
  try {
      xxxx
  } catch (\Exception $exception) {
    Log::error($exception->getMessage());
  } finally {
      $lock->release();
  }
    ```
- 修复Redis实例化的异常



## [v2.1.25] 2022.07.05
- 增加JWT的生成生成Token

## [v2.1.23] 2022.06.24
- `HttpRequest`支持以下方法
  - getRemoteIp()
  - getRemotePort()
  - getLocalIp()
  - getLocalPort()
  - getRealIp()
  - url()
  - fullUrl()
  - isAjax()
  - isPjax()
  - only()
  - except()

## [v2.1.22] 2022.06.23
- HttpRequest上传文件支持UploadFile

## [v2.1.21] 2022.06.21
- 增加`casbin`权限扩展

## [v2.1.20] 2022.06.20
- 增加`event`事件

## [v2.1.18] 2022.06.15
- 修复`HttpRequest`的`getParameter`获取参数失效。
- `Enum` 增加 `getMappings` 方法

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
