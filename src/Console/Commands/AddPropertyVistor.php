<?php
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Console\Commands;

use PhpParser\Builder\Property;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

/**
 * 添加 属性  incrementing/keyType
 */
class AddPropertyVistor extends NodeVisitorAbstract
{
    private $primaryKeyField;

    private $addIncrementingFalse;

    private $addKeyTypeString;

    public function __construct(array $fields, array $stmts)
    {
        $this->fields = $fields;
        $this->stmts = $stmts;
        $this->check();
    }

    /**
     * @param  Node  $node
     * @return void
     */
    public function leaveNode(Node $node)
    {
        $makePropertyNode = function (string $name, $value) {
            return (new Property($name))
                ->makePublic()
                ->setDefault($value)
                ->getNode();
        };
        switch ($node) {
            case $node instanceof Class_:
                if ($this->addIncrementingFalse) {
                    array_unshift($node->stmts, $makePropertyNode('incrementing', false));
                    // code...
                }
                if ($this->addKeyTypeString) {
                    array_unshift($node->stmts, $makePropertyNode('keyType', 'string'));
                }
                break;
        }
    }

    private function check()
    {
        /** @var Field $field */
        foreach ($this->fields as $field) {
            if ($field->isPrimaryKey()) {
                $this->primaryKeyField = $field;
            }
        }
        $nodeFinder = new NodeFinder();
        $this->addIncrementingFalse = ! $this->primaryKeyField->isAutoIncrement() && ! $nodeFinder->findFirst($this->stmts, function (Node $node) {
            return $node instanceof Node\Stmt\Property
                    && $node->props[0]->name->name === 'incrementing';
        });
        $this->addKeyTypeString = $this->primaryKeyField->getTypeMap() == 'string' && ! $nodeFinder->findFirst($this->stmts, function (Node $node) {
            return $node instanceof Node\Stmt\Property
                    && $node->props[0]->name->name === 'keyType';
        });
    }
}
