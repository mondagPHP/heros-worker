<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Valid
{
    /**
     * @param string $class
     * @param string $scene
     */
    public function __construct(public string $class, public string $scene)
    {
    }
}
