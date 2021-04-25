<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\client;

use Workerman\Http\Response;

/**
 * Class HttpClientResponse.
 */
class HttpClientResponse
{
    /**
     * @var Response
     */
    private $workerManResponse;

    public function __construct(Response $response)
    {
        $this->workerManResponse = $response;
    }

    public function getStatusCode(): int
    {
        return $this->workerManResponse->getStatusCode();
    }

    public function getBody(): string
    {
        return (string) $this->workerManResponse->getBody();
    }

    public function getWorkerManResponse(): Response
    {
        return $this->workerManResponse;
    }
}
