<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace Framework\Contract;

interface ViewInterface
{
    /**
     * @param $template
     * @param array $vars
     * @return string
     */
    public static function render($template, array $vars = []): string;
}
