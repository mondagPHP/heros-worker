<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Http;

use Workerman\Protocols\Http\Response;

/**
 * HttpResponse
 */
class HttpResponse extends Response
{
    /**
     * @param int $status
     * @param array $headers
     * @param string $body
     * @return static
     */
    public static function init(
        int    $status = 200,
        array  $headers = [],
        string $body = ''
    ): static {
        return new self($status, $headers, $body);
    }
}
