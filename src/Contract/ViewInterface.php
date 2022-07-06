<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Contract;

interface ViewInterface
{
    /**
     * @param $template
     * @param  array  $vars
     * @return string
     */
    public static function render($template, array $vars = []): string;
}
