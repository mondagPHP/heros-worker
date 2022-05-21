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
    /**
     * desc必填
     * @param string $path
     * @param string $name
     * @param array $method
     */
    public function __construct(public string $path, public string $name, public array $method = ['GET'])
    {
    }
}
