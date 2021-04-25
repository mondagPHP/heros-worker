<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\aop;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Function_ as MagicConstFunction;
use PhpParser\Node\Scalar\MagicConst\Method as MagicConstMethod;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;

/**
 * Class ProxyNodeVisitor.
 */
class ProxyNodeVisitor extends NodeVisitorAbstract
{
    /** @var string */
    private $currentClass = '';

    /** @var ProxyCollects */
    private $proxyCollects;

    private $extends;

    private $class;

    public function __construct(ProxyCollects $proxyCollects)
    {
        $this->proxyCollects = $proxyCollects;
    }

    /**
     * @return mixed
     */
    public function beforeTraverse(array $nodes)
    {
        foreach ($nodes as $namespace) {
            foreach ($namespace->stmts as $class) {
                if ($class instanceof Node\Stmt\Class_) {
                    $this->class = $class->name;
                    if ($class->extends) {
                        $this->extends = $class->extends;
                    }
                    $this->currentClass = $namespace->name->toString() . '\\' . $class->name;
                    return null;
                }
            }
        }
        return null;
    }

    /**
     * @return null|Class_|ClassMethod|Node
     */
    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\ClassMethod:
                if ($this->shouldRewrite($node)) {
                    return $this->rewriteMethod($node);
                }
                return $this->formatMethod($node);
            case $node instanceof Node\Stmt\Class_ && $this->shouldUseTrait():
                // Add use proxy traits.
                $stmts = $node->stmts;
                if ($stmt = $this->buildProxyCallTraitUseStatement()) {
                    array_unshift($stmts, $stmt);
                }
                $node->stmts = $stmts;
                unset($stmts);
                return $node;
            case ($node instanceof StaticPropertyFetch || $node instanceof StaticCall) && $this->extends:
                if ($node->class instanceof Node\Name && 'parent' === $node->class->toString()) {
                    $node->class = new Name($this->extends->toCodeString());
                    return $node;
                }
        }
        return null;
    }

    /**
     * Format a normal class method of no need proxy call.
     * @return ClassMethod
     */
    private function formatMethod(ClassMethod $node)
    {
        if ('__construct' === $node->name->toString()) {
            // Rewrite parent::__construct to class::__construct.
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Expression && $stmt->expr instanceof Node\Expr\StaticCall) {
                    $class = $stmt->expr->class;
                    if ($class instanceof Node\Name && 'parent' === $class->toString()) {
                        $stmt->expr->class = new Node\Name($this->extends->toCodeString());
                    }
                }
            }
        }

        return $node;
    }

    /**
     * Rewrite a normal class method to a proxy call method,
     * include normal class method and static method.
     */
    private function rewriteMethod(ClassMethod $node): ClassMethod
    {
        // Build the static proxy call method base on the original method.
        $shouldReturn = true;
        $returnType = $node->getReturnType();
        if ($returnType instanceof Identifier && 'void' === $returnType->name) {
            $shouldReturn = false;
        }
        $class = $this->class->toString();
        $staticCall = new StaticCall(new Name('self'), '_proxyCall', [
            // __CLASS__
            new Node\Arg(new ClassConstFetch(new Name($class), new Identifier('class'))),
            // __FUNCTION__
            new Arg(new MagicConstFunction()),
            // self::getParamMap(OriginalClass::class, __FUNCTION, func_get_args())
            new Arg(new StaticCall(new Name('self'), '_getArguments', [
                new Node\Arg(new ClassConstFetch(new Name($class), new Identifier('class'))),
                new Arg(new MagicConstFunction()),
                new Arg(new FuncCall(new Name('func_get_args'))),
            ])),
            // A closure that wrapped original method code.
            new Arg(new Closure([
                'params' => value(function () use ($node) {
                    // Transfer the variadic variable to normal variable at closure argument. ...$params => $parms
                    $params = $node->getParams();
                    foreach ($params as $key => $param) {
                        if ($param instanceof Node\Param && $param->variadic) {
                            $newParam = clone $param;
                            $newParam->variadic = false;
                            $params[$key] = $newParam;
                        }
                    }
                    return $params;
                }),
                'stmts' => $node->stmts,
            ])),
        ]);
        $stmts = $this->unshiftMagicMethods([]);
        if ($shouldReturn) {
            $stmts[] = new Return_($staticCall);
        } else {
            $stmts[] = new Expression($staticCall);
        }
        $node->stmts = $stmts;
        return $node;
    }

    private function unshiftMagicMethods($stmts = []): array
    {
        $magicConstFunction = new Expression(new Assign(new Variable('__function__'), new MagicConstFunction()));
        $magicConstMethod = new Expression(new Assign(new Variable('__method__'), new MagicConstMethod()));
        array_unshift($stmts, $magicConstFunction, $magicConstMethod);
        return $stmts;
    }

    /**
     * Build `use ProxyTrait;`.
     */
    private function buildProxyCallTraitUseStatement(): ?TraitUse
    {
        $traits = [new Name('\\' . ProxyCallTrait::class)];
        return new TraitUse($traits);
    }

    private function shouldRewrite(Node $node): bool
    {
        return $this->currentClass && $this->proxyCollects->shouldRewrite($this->currentClass, $node->name->toString());
    }

    private function shouldUseTrait(): bool
    {
        return $this->currentClass && $this->proxyCollects->shouldUseTrait($this->currentClass);
    }
}
