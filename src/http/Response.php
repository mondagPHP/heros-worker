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
     * @param int $code
     * @return Response
     */
    public function redirect($url, $code = 302): self
    {
        $this->status($code);
        $this->workerResponse->header('Location', $url);
        return $this;
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
     * @return $this
     * @throws \framework\exception\FileNotFoundException
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
     * @return $this
     * @throws \framework\exception\FileNotFoundException
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
                if (method_exists($this->content, '__toString')) {
                    $content = (string)$this->content;
                } else {
                    $content = json_encode($this->content);
                }
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
        $this->workerResponse->withBody($content);
        return $this->workerResponse;
    }
}
