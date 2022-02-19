<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Http;

class Response extends \Workerman\Protocols\Http\Response
{
    /**
     * 初始化response
     * @param int $status
     * @param array $headers
     * @param string $body
     * @return static
     */
    public static function init(
        int    $status = 200,
        array  $headers = [],
        string $body = ''
    ): self {
        return new self(
            $status,
            $headers,
            $body
        );
    }
}
