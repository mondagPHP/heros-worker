<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Enums;

use Framework\Annotation\Message;
use Illuminate\Support\Str;

class ReflectionAdapter implements AdapterInterface
{
    protected $class = '';

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function getAnnotationsByName($name, $properties)
    {
        $result = [];
        foreach ($properties as $key => $val) {
            if (Str::startsWith($key, 'ENUM_')) {
                $ret = new \ReflectionProperty($this->class, $key);
                $reflectionAttributes = $ret->getAttributes(Message::class);
                if ($reflectionAttributes) {
                    $attribute = $reflectionAttributes[0];
                    $result[$val] = $attribute->newInstance()->value;
                }
            }
        }
        return $result;
    }
}
