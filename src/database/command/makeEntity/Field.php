<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\database\command\makeEntity;

/**
 * Class Field
 * @package framework\database\command\makeEntity
 */
class Field
{
    public const PRI = 'PRI';

    private $name = '';

    private $type = '';

    private $isPrimaryKey = false;

    private $isAutoIncrement = false;

    private $comment = '';

    public function __construct(array $fieldInfos)
    {
        $this->init($fieldInfos);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    /**
     * @return bool
     */
    public function isAutoIncrement(): bool
    {
        return $this->isAutoIncrement;
    }

    /**
     * @return string
     */
    public function toPropertyString(): string
    {
        return sprintf("* @property %s $%s %s\n", $this->getTypeMap(), $this->name(), $this->comment);
    }

    /**
     * @return string
     */
    public function getTypeMap(): string
    {
        switch ($this->type) {
            case 'date':
            case 'datetime':
                if (in_array($this->name(), ['created_at', 'updated_at'])) {
                    return 'Carbon';
                }
                    return 'string';
                
            case 'int':
            case 'tinyint':
            case 'integer':
                return 'int';
            default:
                return 'string';
        }
    }

    /**
     * @param array $fieldInfos
     */
    private function init(array $fieldInfos): void
    {
        $this->name = $fieldInfos['Field'];
        preg_match('/[a-z]+/', $fieldInfos['Type'], $matches);
        $this->type = $matches[0];
        $this->isPrimaryKey = $fieldInfos['Key'] === self::PRI;
        $this->comment = $fieldInfos['Comment'];
        $this->isAutoIncrement = $fieldInfos['Extra'] === 'auto_increment';
    }
}
