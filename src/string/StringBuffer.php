<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\string;

/**
 * Class StringBuffer.
 */
class StringBuffer
{
    private $strMap = [];

    public function __construct(?string $str = null)
    {
        if (null !== $str) {
            $this->append($str);
        }
    }

    public function isEmpty(): bool
    {
        return 0 == count($this->strMap);
    }

    //append content
    public function append($str = null)
    {
        array_push($this->strMap, $str);
    }

    //append line
    public function appendLine($str = null)
    {
        $this->append($str . "\n");
    }

    //append line with tab symbol
    public function appendTab($str = null, $tabNum = 1)
    {
        $tab = '';
        for ($i = 0; $i < $tabNum; ++$i) {
            $tab .= "\t";
        }
        $this->appendLine($tab . $str);
    }

    public function toString(): string
    {
        foreach ($this->strMap as $key => $value) {
            if (is_array($value)) {
                $this->strMap[$key] = implode('', $value);
            }
        }
        return implode('', $this->strMap);
    }
}
