<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Http;

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
