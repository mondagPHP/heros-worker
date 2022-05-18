<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
