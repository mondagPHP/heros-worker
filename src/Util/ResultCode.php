<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Util;

class ResultCode
{
    //成功
    public const SUCCESS = ['code' => '000', 'message' => '操作成功'];

    //错误
    public const ERROR = ['code' => '001', 'message' => '操作失败'];
}
