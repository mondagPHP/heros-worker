<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\exception;

use framework\http\Response;

class ValidateException extends \Exception
{
    public function render(): Response
    {
        return \response($this->getMessage(), 200);
    }
}
