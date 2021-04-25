<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\core;

/**
 * 配置文件
 * Class Config.
 */
class Config
{
    /**
     * @var array 配置文件
     */
    private static $config = [];

    /**
     * 加载配置文件.
     */
    public static function load(string $configPath, array $excludeFile = [])
    {
        $handler = opendir($configPath);
        while (($filename = readdir($handler)) !== false) {
            if ('.' != $filename && '..' != $filename) {
                $basename = basename($filename, '.php');
                if (in_array($basename, $excludeFile)) {
                    continue;
                }
                self::$config[$basename] = require_once $configPath . '/' . $filename;
            }
        }
        closedir($handler);
    }

    /**
     * 获取配置文件.
     * @param  null  $key     键
     * @param  null  $default 默认值
     * @return mixed
     */
    public static function get($key = null, $default = null)
    {
        if (null === $key) {
            return self::$config;
        }
        $key_array = explode('.', $key);
        $value = self::$config;
        foreach ($key_array as $index) {
            if (! isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }

    /**
     * 重新加载.
     * @param $configPath
     * @param array $excludeFile
     */
    public static function reload($configPath, $excludeFile = [])
    {
        self::$config = [];
        self::load($configPath, $excludeFile);
    }
}
