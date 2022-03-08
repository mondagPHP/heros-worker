<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Session;

use Framework\Exception\HerosException;

class RedisSessionHandler extends \Workerman\Protocols\Http\Session\RedisSessionHandler
{
    public function __construct($config)
    {
        if (! extension_loaded('redis')) {
            throw new HerosException('please install redis ext');
        }
        parent::__construct($config);
    }
}
