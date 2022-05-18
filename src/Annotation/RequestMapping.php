<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
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
     * desc必填
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
