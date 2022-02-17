<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */

namespace Framework\Http;

use Workerman\Protocols\Http\Response as WorkerResponse;

/**
 * Class Response
 * @package Framework\Http
 * @method  mixed header($name, $value)
 * @method  mixed withHeader($name, $values)
 * @method mixed withHeaders($headers)
 * @method mixed withoutHeader($name)
 * @method mixed getHeader($name)
 * @method mixed getHeaders()
 * @method mixed withStatus($code, $reasonPhrase = null)
 * @method mixed getStatusCode()
 * @method mixed getReasonPhrase()
 * @method mixed withProtocolVersion()
 * @method mixed withBody()
 * @method mixed rawBody()
 * @method mixed withFile($file, $offset = 0, $length = 0)
 * @method mixed cookie($name, $value = '', $max_age = 0, $path = '', $domain = '', $secure = false, $httpOnly = false, $sameSite = false)
 */
class Response
{
    /**
     * @var WorkerResponse $workerResponse
     */
    private WorkerResponse $workerResponse;

    /**
     * HttpResponse constructor.
     */
    private function __construct(WorkerResponse $response)
    {
        $this->workerResponse = $response;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->workerResponse->{$name}(... $arguments);
    }

    public static function init($status = 200, $headers = array(), $body = ''): self
    {
        return new self(new WorkerResponse($status, $headers, $body));
    }

    /**
     * @return WorkerResponse
     */
    public function originResponse(): WorkerResponse
    {
        return $this->workerResponse;
    }
}
