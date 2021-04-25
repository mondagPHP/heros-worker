<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\aop;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Class ProxyClassVisitor.
 */
class ProxyClassVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private $proxyClassName;

    public function __construct(string $proxyClassName)
    {
        if (false !== strpos($proxyClassName, '\\')) {
            $exploded = explode('\\', $proxyClassName);
            $proxyClassName = end($exploded);
        }
        $this->proxyClassName = $proxyClassName;
    }

    public function leaveNode(Node $node)
    {
        // Rewirte the class name and extends the original class.
        if ($node instanceof Node\Stmt\Class_ && ! $node->isAnonymous()) {
            $node->extends = new Node\Name($node->name->name);
            $node->name = new Node\Identifier($this->proxyClassName);
            return $node;
        }
    }
}
