<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
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
