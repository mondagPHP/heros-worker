<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\view;

/**
 * Interface IView.
 */
interface IView
{
    /**
     * @param $template
     * @return false|string
     */
    public static function render($template, array $vars);
}
