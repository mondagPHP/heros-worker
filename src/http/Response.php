<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\http;

use framework\exception\FileNotFoundException;
use Workerman\Protocols\Http\Response as WorkerResponse;

/**
 * Class Response.
 */
class Response
{
    private $content;

    /**
     * @var WorkerResponse
     */
    private $workerResponse;

    /**
     * HttpResponse constructor.
     */
    public function __construct(WorkerResponse $response)
    {
        $this->workerResponse = $response;
    }

    public static function init(WorkerResponse $response): self
    {
        return new self($response);
    }

    /**
     * 设置头.
     * @param $name
     * @param $value
     * @return $this
     */
    public function header($name, $value): self
    {
        $this->workerResponse->withHeader($name, $value);
        return $this;
    }

    /**
     * @return $this
     */
    public function status(int $code): self
    {
        $this->workerResponse->withStatus($code);
        return $this;
    }

    /**
     * @param $url
     */
    public function redirect($url): void
    {
        $this->workerResponse->header('Location', $url);
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     *               设置cookie
     */
    public function cookie($name, $value): self
    {
        $this->workerResponse->cookie($name, $value);
        return $this;
    }

    /**
     * 设置body.
     * @param $content
     * @return $this
     */
    public function body($content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * 返回文件.
     * @throws \framework\exception\FileNotFoundException
     * @return $this
     */
    public function file(string $file): self
    {
        if (! file_exists($file)) {
            throw new FileNotFoundException('文件不存在!');
        }
        $this->workerResponse->withFile($file);
        return $this;
    }

    /**
     * @throws \framework\exception\FileNotFoundException
     * @return $this
     */
    public function download(string $file, string $downloadName = ''): self
    {
        if (! file_exists($file)) {
            throw new FileNotFoundException('文件不存在!');
        }
        $this->workerResponse->withFile($file);
        if ($downloadName) {
            $this->header('Content-Disposition', "attachment; filename=\"{$downloadName}\"");
        }
        return $this;
    }

    public function end(): WorkerResponse
    {
        switch (gettype($this->content)) {
            case 'object':
                $this->header('Content-Type', 'application/json;charset=utf-8');
                $content = (string)$this->content;
                break;
            case 'array':
                $this->header('Content-Type', 'application/json;charset=utf-8');
                $content = json_encode($this->content);
                break;
            case 'string':
            default:
                $content = $this->content;
                break;
        }
        $enableGzip = config('server.enable_gzip', false);
        if ($enableGzip) {
            $content = gzencode($content);
        }
        $this->workerResponse->withBody($content);
        return $this->workerResponse;
    }
}
