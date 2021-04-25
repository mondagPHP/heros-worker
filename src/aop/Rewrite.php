<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\aop;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

/**
 * Class RewriteClass.
 */
class Rewrite
{
    /** @var Config */
    private $config;

    /** @var ProxyCollects */
    private $proxyCollects;

    /** @var Parser */
    private $parser;

    /** @var NodeTraverser */
    private $traverser;

    /** @var Standard */
    private $prettyPrinter;

    public function __construct(Config $config, ProxyCollects $proxyCollects)
    {
        $this->config = $config;
        $this->proxyCollects = $proxyCollects;
    }

    public function rewrite()
    {
        foreach ($this->proxyCollects->getClassMap() as $className => &$item) {
            foreach ($item['methods'] as $method) {
                if ('*' === $method) {
                    continue;
                }
                $this->init();
                $code = file_get_contents($item['filePath']);
                $ast = $this->parser->parse($code);
                $proxyClassName = $className . '_' . md5($className);
                $this->traverser->addVisitor(new ProxyClassVisitor($proxyClassName));
                $this->traverser->addVisitor(new ProxyNodeVisitor($this->proxyCollects));
                $newAst = $this->traverser->traverse($ast);
                $newCode = $this->prettyPrinter->prettyPrintFile($newAst);
                $filePath = $this->getProxyFilePath($className);
                file_put_contents($filePath, $newCode);
                $this->proxyCollects->setNewPath($className, $filePath);
                $this->proxyCollects->setProxyClassName($className, $proxyClassName, $filePath);
            }
        }
    }

    /**
     * @param $className
     */
    protected function getProxyFilePath($className): string
    {
        return $this->config->getPath() . '/' . str_replace('\\', '_', $className) . '.proxy.php';
    }

    private function init()
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->traverser = new NodeTraverser();
        $this->prettyPrinter = new Standard();
    }
}
