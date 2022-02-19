<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 * @contact  mondagroup_php@163.com
 */
namespace Framework\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_METHOD)]
class Valid
{
    public string $class;

    public string $scene;

    /**
     * @param string $class
     * @param string $scene
     */
    public function __construct(string $class, string $scene)
    {
        $this->class = $class;
        $this->scene = $scene;
    }
}
