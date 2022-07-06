<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Enums;

interface AdapterInterface
{
    public function __construct($class);

    public function getAnnotationsByName($properties);
}
