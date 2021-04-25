<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\aop;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use framework\bootstrap\Container;

/**
 * Class ClassLoader.
 */
class ClassLoader
{
    public static $proxyClasses = [];

    public static $aspectClasses = [];

    public static $classMap = [];
    /** @var ComposerClassLoader */
    private $composerClassLoader;

    /** @var Config */
    private $config;

    public function __construct(ComposerClassLoader $composerClassLoader, Config $config)
    {
        $this->composerClassLoader = $composerClassLoader;
        $this->config = $config;
        $this->config->parse();
        $proxyCollects = new ProxyCollects();
        $aspectCollects = new AspectCollects($config, $this->composerClassLoader);
        $aspectCollects->collectProxy($proxyCollects);
        (new Rewrite($config, $proxyCollects))->rewrite();
        self::$proxyClasses = $proxyCollects->getProxyClasses();
        self::$aspectClasses = $aspectCollects->getAspectsClass();
        self::$classMap = $proxyCollects->getClassMap();
    }

    /**
     * register class.
     */
    public static function reload(array $config = []): void
    {
        $loaders = spl_autoload_functions();
        $selfLoader = null;
        foreach ($loaders as $loader) {
            if (isset($loader[0]) && $loader[0] instanceof ComposerClassLoader) {
                $composerLoader = $loader[0];
                $selfLoader = new self($composerLoader, new Config($config));
                spl_autoload_register([$selfLoader, 'loadClass'], true, true);
                spl_autoload_unregister($loader);
            }
        }
    }

    public static function init(): void
    {
        Container::start();
        foreach (self::$proxyClasses as $proxyClass => $class) {
            Container::instance()->set($class[1], new $proxyClass());
        }
    }

    /**
     * @param $class
     */
    public function loadClass($class): bool
    {
        if (isset(self::$proxyClasses[$class]) && file_exists(self::$proxyClasses[$class][0])) {
            $file = self::$proxyClasses[$class]['0'];
        } else {
            $file = $this->composerClassLoader->findFile($class);
        }
        if ($file) {
            includeFile($file);
            return true;
        }
        return false;
    }
}

/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 * @param mixed $file
 */
function includeFile($file)
{
    include_once $file;
}
