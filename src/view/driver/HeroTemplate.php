<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\view\driver;

use framework\exception\HeroException;
use framework\file\FileUtils;
use framework\view\IView;

/**
 * Class HeroTemplate.
 */
class HeroTemplate implements IView
{
    /**
     * @var array
     */
    protected static $_vars = [];

    /**
     * 模板编译规则.
     * @var array
     */
    private static $tempRules = [
        /*
         * 输出变量,数组
         * {$varname}, {$array['key']}
         */
        '/{\$([^\}|\.]{1,})}/i' => '<?php echo \$${1}?>',
        // 以 {$array.key} 形式输出一维数组元素
        '/{\$([0-9a-z_]{1,})\.([0-9a-z_]{1,})}/i' => '<?php echo \$${1}[\'${2}\']?>',
        // 以 {$array.key1.key2} 形式输出二维数组
        '/{\$([0-9a-z_]{1,})\.([0-9a-z_]{1,})\.([0-9a-z_]{1,})}/i' => '<?php echo \$${1}[\'${2}\'][\'${3}\']?>',

        //for 循环
        '/{for ([^\}]+)}/i' => '<?php for ${1} {?>',
        '/{\/for}/i' => '<?php } ?>',

        /*
         * foreach key => value 形式循环输出
         * foreach ( $array as $key => $value )
         */
        '/{loop\s+\$([^\}]{1,})\s+\$([^\}]{1,})\s+\$([^\}]{1,})\s*}/i' => '<?php foreach ( \$${1} as \$${2} => \$${3} ) { ?>',
        '/{\/loop}/i' => '<?php } ?>',

        /*
         * foreach 输出
         * foreach ( $array as $value )
         */
        '/{loop\s+\$(.*?)\s+\$([0-9a-z_]{1,})\s*}/i' => '<?php foreach ( \$${1} as \$${2} ) { ?>',
        '/{\/loop}/i' => '<?php } ?>',

        /*
         * {run}标签： 执行php表达式
         * {expr}标签：输出php表达式
         * {url}标签：输出格式化的url
         * {date}标签：根据时间戳输出格式化日期
         * {cut}标签：裁剪字指定长度的字符串,注意截取的格式是UTF-8,多余的字符会用...表示
         */
        '/{run\s+(.*?)}/i' => '<?php ${1} ?>',
        '/{expr\s+(.*?)}/i' => '<?php echo ${1} ?>',
        '/{date\s+(.*?)(\s+(.*?))?}/i' => '<?php echo \framework\view\driver\HeroTemplate::getDate(${1}, "${2}") ?>',
        '/{cut\s+(.*?)(\s+(.*?))?}/i' => '<?php echo \framework\view\driver\HeroTemplate::cutString(${1}, "${2}") ?>',

        /*
         * if语句标签
         * if () {} elseif {}
         */
        '/{if\s+(.*?)}/i' => '<?php if ( ${1} ) { ?>',
        '/{else}/i' => '<?php } else { ?>',
        '/{elseif\s+(.*?)}/i' => '<?php } elseif ( ${1} ) { ?>',
        '/{\/if}/i' => '<?php } ?>',

        /*
         * 导入模板
         * require|include
         */
        '/{(require|include)\s{1,}([0-9a-z_\.\:]{1,})\s*}/i' => '<?php include \framework\view\driver\HeroTemplate::getIncludePath(\'${2}\')?>',

        // 引入静态资源 css file,javascript file
        '/{(res):([a-z]{1,})\s+([^\}]+)\s*}/i' => '<?php echo \framework\view\driver\HeroTemplate::importResource(\'${2}\', "${3}")?>',
    ];

    /**
     * 静态资源模板
     * @var array
     */
    private static $resTemplate = [
        'css' => "<link rel=\"stylesheet\" type=\"text/css\" href=\"{url}\" />\n",
        'less' => "<link rel=\"stylesheet/less\" type=\"text/css\" href=\"{url}\" />\n",
        'js' => "<script charset=\"utf-8\" type=\"text/javascript\" src=\"{url}\"></script>\n",
    ];

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
        return static::display($template, $vars);
    }

    /**
     * 获取include路径
     * 如果没有申明应用则默认以当前的应用为相对路径.
     * @param  string        $tempPath 被包含的模板路径
     * @throws HeroException
     */
    public static function getIncludePath(string $tempPath): string
    {
        $viewPath = config('view.view_path');
        $viewCachePath = config('view.cache_path');
        $filename = str_replace('.', '/', $tempPath) . '.' . \config('view.view_suffix', 'html');   //模板文件名称
        $tempFile = $viewPath . $filename;
        $compileFile = $viewCachePath . $filename . '.php';
        //编译文件
        static::compileTemplate($tempFile, $compileFile);
        return $compileFile;
    }

    /**
     * 引进静态资源如css，js.
     * @param string $type 资源类别
     * @param string $path 资源路径
     */
    public static function importResource(string $type, string $path): string
    {
        $src = '/' . $path;
        $template = self::$resTemplate[$type];
        return str_replace('{url}', $src, $template);
    }

    /**
     * 获取日期
     */
    public static function getDate(string $time, string $format): string
    {
        if (! $format) {
            $format = 'Y-m-d H:i:s';
        }
        return date($format, $time);
    }

    /**
     * 裁剪字符串，使用utf-8编码裁剪.
     * @param string $str    要裁剪的字符串
     * @param int    $length 字符串长度
     */
    public static function cutString(string $str, int $length): string
    {
        if (mb_strlen($str, 'UTF-8') <= $length) {
            return $str;
        }
        return mb_substr($str, 0, $length, 'UTF-8') . '...';
    }

    /**
     * @param $vars
     * @return false|string
     */
    private static function display(string $tempFile, $vars)
    {
        static $viewSuffix;
        $viewSuffix = $viewSuffix ?: \config('view.view_suffix', 'html');
        $tempFile .= '.' . $viewSuffix;
        $compileFile = $tempFile . '.php';
        $viewPath = config('view.view_path');
        $viewCachePath = config('view.cache_path');
        if (! file_exists($viewPath . $tempFile)) {
            throw new HeroException('要编译的模板[' . $viewPath . $tempFile . '] 不存在！');
        }
        static::compileTemplate($viewPath . $tempFile, $viewCachePath . $compileFile);
        return static::getExecutedHtml($viewCachePath . $compileFile, $vars);
    }

    /**
     * 获取页面执行后的代码
     * @param $vars
     * @return false|string
     */
    private static function getExecutedHtml(string $compileTemplate, $vars)
    {
        \extract(static::$_vars);
        \extract($vars);
        \ob_start();
        try {
            include $compileTemplate;
        } catch (\Throwable $e) {
            echo $e;
        }
        static::$_vars = [];
        return \ob_get_clean();
    }

    /**
     * 编译模板
     * @param  string        $tempFile    模板文件路径
     * @param  string        $compileFile 编译文件路径
     * @throws HeroException
     */
    private static function compileTemplate(string $tempFile, string $compileFile)
    {
        $cache = config('view.cache', 0);
        //根据缓存情况编译模板
        if (! file_exists($compileFile) || (1 == $cache && filemtime($compileFile) < filemtime($tempFile)) || 0 == $cache) {
            //获取模板文件
            $content = @file_get_contents($tempFile);
            if (false == $content) {
                throw new HeroException('加载模板文件 {' . $tempFile . '} 失败！请在相应的目录建立模板文件。');
            }
            //替换模板
            $content = preg_replace(array_keys(self::$tempRules), self::$tempRules, $content);
            //生成编译目录
            if (! file_exists(dirname($compileFile))) {
                FileUtils::makeFileDirs(dirname($compileFile));
            }
            //生成php文件
            if (! file_put_contents($compileFile, $content, LOCK_EX)) {
                throw new HeroException("生成编译文件 {$compileFile} 失败。");
            }
        }
    }
}
