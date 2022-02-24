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
 * @method self header($name, $value)
 * @method self withHeader($name, $value)
 * @method self withHeaders($headers)
 * @method self withoutHeader($name)
 * @method null|array|string getHeader($name)
 * @method array getHeaders()
 * @method self withStatus($code, $reasonPhrase = null)
 * @method int getStatusCode()
 * @method string getReasonPhrase()
 * @method self withProtocolVersion($version)
 * @method self withBody($body)
 * @method string rawBody()
 * @method self withFile($file, $offset = 0, $length = 0)
 * @method self cookie($name, $value = '', $max_age = 0, $path = '', $domain = '', $secure = false, $http_only = false, $same_site = false)
 *
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
    ): static
    {
        return new self($status, $headers, $body);
    }


    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->{$name}(...$arguments);
    }
}
