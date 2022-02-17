<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace Framework\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestMapping
{
    public string $path;

    public array $method;

    public string $desc;

    /**
     * desc必填，保证代码可阅读
     * @param string $path
     * @param array $method
     * @param string $desc
     */
    public function __construct(string $path, string $desc, array $method = ['GET'])
    {
        $this->path = $path;
        $this->method = $method;
        $this->desc = $desc;
    }
}
