<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestMapping
{
    public string $path;

    public array $method;

    public string $name;

    /**
     * desc必填，保证代码可阅读
     * @param string $path
     * @param string $name
     * @param array $method
     */
    public function __construct(string $path, string $name, array $method = ['GET'])
    {
        $this->path = $path;
        $this->method = $method;
        $this->name = $name;
    }
}
