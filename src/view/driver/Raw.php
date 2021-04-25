<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\view\driver;

use framework\view\IView;

/**
 * Class Raw.
 */
class Raw implements IView
{
    /**
     * @var array
     */
    protected static $_vars = [];

    /**
     * @param $name
     * @param null $value
     */
    public static function assign($name, $value = null)
    {
        static::$_vars = \array_merge(static::$_vars, \is_array($name) ? $name : [$name => $value]);
    }

    /**
     * @param $template
     * @param $vars
     * @return false|string
     */
    public static function render($template, $vars)
    {
        static $viewSuffix;
        $viewSuffix = $viewSuffix ?: \config('view.view_suffix', 'html');
        $viewPath = config('view.view_path', BASE_PATH . '/view/') . "{$template}.{$viewSuffix}";
        \extract(static::$_vars);
        \extract($vars);
        \ob_start();
        try {
            include $viewPath;
        } catch (\Throwable $e) {
            echo $e;
        }
        static::$_vars = [];
        return \ob_get_clean();
    }
}
