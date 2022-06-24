<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Http;

use Framework\Application;
use Workerman\Protocols\Http\Request;

/**
 * Class Request
 * @package Framework\Http
 */
class HttpRequest extends Request
{
    private array $routerParams = [];

    private array $injectObject = [];

    /**
     * @return array
     */
    public function getParams(): array
    {
        return array_merge($this->get(), $this->post(), $this->routerParams);
    }

    /**
     * @return array
     */
    public function getRouterParams(): array
    {
        return $this->routerParams;
    }

    /**
     * @param array $routerParams
     */
    public function setRouterParams(array $routerParams): void
    {
        $this->routerParams = $routerParams;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function getParameter(string $name, $default = null): mixed
    {
        return $this->getParams()[$name] ?? $default;
    }

    /**
     * @param object $object
     * @return void
     */
    public function pushInjectObject(object $object)
    {
        $this->injectObject[get_class($object)] = $object;
    }

    /**
     * @return array
     */
    public function getInjectObject(): array
    {
        return $this->injectObject;
    }

    /**
     * @param string|null $name
     * @return null|UploadFile[]|UploadFile
     */
    public function file($name = null): array|UploadFile|null
    {
        $files = parent::file($name);
        if (null === $files) {
            return $name === null ? [] : null;
        }
        if ($name !== null) {
            if (\is_array(\current($files))) {
                return $this->parseFiles($files);
            }
            return $this->parseFile($files);
        }
        $uploadFiles = [];
        foreach ($files as $name => $file) {
            if (\is_array(\current($file))) {
                $uploadFiles[$name] = $this->parseFiles($file);
            } else {
                $uploadFiles[$name] = $this->parseFile($file);
            }
        }
        return $uploadFiles;
    }

    /**
     * @return string
     */
    public function getRemoteIp(): string
    {
        return Application::$connection->getRemoteIp();
    }

    /**
     * @return int
     */
    public function getRemotePort(): int
    {
        return Application::$connection->getRemotePort();
    }

    /**
     * @return string
     */
    public function getLocalIp(): string
    {
        return Application::$connection->getLocalIp();
    }

    /**
     * @return int
     */
    public function getLocalPort(): int
    {
        return Application::$connection->getLocalPort();
    }

    /**
     * @param bool $safeMode
     * @return string
     */
    public function getRealIp(bool $safeMode = true): string
    {
        $remoteIp = $this->getRemoteIp();
        if ($safeMode && ! static::isIntranetIp($remoteIp)) {
            return $remoteIp;
        }
        return $this->header('client-ip', $this->header(
            'x-forwarded-for',
            $this->header('x-real-ip', $this->header(
                'x-client-ip',
                $this->header('via', $remoteIp)
            ))
        ));
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return '//' . $this->host() . $this->path();
    }

    /**
     * @return string
     */
    public function fullUrl(): string
    {
        return '//' . $this->host() . $this->uri();
    }

    /**
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * @return bool
     */
    public function isPjax(): bool
    {
        return (bool)$this->header('X-PJAX');
    }

    /**
     * @param string $ip
     * @return bool
     */
    public static function isIntranetIp(string $ip): bool
    {
        // Not validate ip .
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        // Is intranet ip ? For IPv4, the result of false may not be accurate, so we need to check it manually later .
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        // Manual check only for IPv4 .
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        // Manual check .
        $reserved_ips = [
            1681915904 => 1686110207, // 100.64.0.0 -  100.127.255.255
            3221225472 => 3221225727, // 192.0.0.0 - 192.0.0.255
            3221225984 => 3221226239, // 192.0.2.0 - 192.0.2.255
            3227017984 => 3227018239, // 192.88.99.0 - 192.88.99.255
            3323068416 => 3323199487, // 198.18.0.0 - 198.19.255.255
            3325256704 => 3325256959, // 198.51.100.0 - 198.51.100.255
            3405803776 => 3405804031, // 203.0.113.0 - 203.0.113.255
            3758096384 => 4026531839, // 224.0.0.0 - 239.255.255.255
        ];
        $ip_long = ip2long($ip);
        foreach ($reserved_ips as $ip_start => $ip_end) {
            if (($ip_long >= $ip_start) && ($ip_long <= $ip_end)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $keys
     * @return array
     */
    public function only(array $keys): array
    {
        $all = $this->getParams();
        $result = [];
        foreach ($keys as $key) {
            if (isset($all[$key])) {
                $result[$key] = $all[$key];
            }
        }
        return $result;
    }

    /**
     * @param array $keys
     * @return array
     */
    public function except(array $keys): array
    {
        $all = $this->getParams();
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        return $all;
    }

    /**
     * @param $file
     * @return UploadFile
     */
    protected function parseFile($file): UploadFile
    {
        return new UploadFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
    }

    /**
     * @param array $files
     * @return array
     */
    protected function parseFiles(array $files): array
    {
        $uploadFiles = [];
        foreach ($files as $key => $file) {
            if (\is_array(\current($file))) {
                $uploadFiles[$key] = $this->parseFiles($file);
            } else {
                $uploadFiles[$key] = $this->parseFile($file);
            }
        }
        return $uploadFiles;
    }
}
