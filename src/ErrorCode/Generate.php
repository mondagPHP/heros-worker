<?php

namespace Framework\ErrorCode;

/**
 * 生成code
 */
class Generate
{
    private mixed $errorClass;
    private string $root = '';
    private int $minNum = 10000;
    private string $systemNumCode = "200";

    public function __construct($config)
    {
        try {
            if (!isset($config["class"]) || !is_object($config["class"])) {
                throw new \Exception("请选择需要生成的错误类对象");
            }
            if (!isset($config["system_number"])) {
                throw new \Exception("请配置系统编码");
            }
            $this->errorClass = $config["class"];
            $this->root = app_path();
            $this->systemNumCode = Code::getSystemCode((string)$config["system_number"]);

            if (isset($config["start_min_number"]) && $config["start_min_number"] > 0) {
                $this->minNum = intval($config["start_min_number"]);
            }

        } catch (\Exception $e) {
            $this->errorReport($e->getMessage());
        }
    }

    public function run()
    {
        try {
            try {
                $reflection = new \ReflectionClass($this->errorClass);
            } catch (\Exception) {
                throw new \Exception("类不存在");
            }
            $classNameSpaceName = $reflection->getNamespaceName();
            $tmp = explode("\\", $reflection->getName());
            $className = end($tmp);
            $classPath = $reflection->getFileName();
            if (!is_writable($classPath)) {
                throw new \Exception("文件不可写");
            }
            $start = $className . '::';
            $return = shell_exec("find $this->root -name '*.php' ! -path './vendor' | xargs grep '$start'");
            if (!isset($return)) {
                // 没有找到数据直接返回
                return;
            }
            $arr = explode("\n", $return);
            $codeList = [];
            foreach ($arr as $str) {
                $str = str_replace(array(" "), array(""), $str);
                $match = [];
                preg_match("/$start(.*?)[,|)|;]/s", $str, $match);
                if (isset($match[1])) {
                    $codeList[$match[1]] = 1;
                }
            }

            $max = $this->minNum;
            $write_list = [];
            foreach ($reflection->getConstants() as $const_name => $val) {
                $currentRealNumber = (int)\substr((string)$val, 4);
                $write_list[$const_name] = $val;
                unset($codeList[$const_name]);
                if ($currentRealNumber > $max) {
                    $max = $currentRealNumber;
                };
            }
            foreach ($codeList as $name => $val) {
                $currentNumber = ++$max;
                $currentErrorNumber = $this->systemNumCode . ((string)$currentNumber);
                $write_list[$name] = -(int)$currentErrorNumber;
            }
            $template = <<<EOT
<?php
namespace $classNameSpaceName;

class $className
{
EOT;
            $template .= "\n";
            foreach ($write_list as $name => $val) {
                $template .= "    const $name = $val;\n";
            }
            $template .= "}";
            file_put_contents($classPath, $template);
        } catch (\Exception $e) {
            $this->errorReport($e->getMessage());
        }
    }


    /**
     * @param $msg
     */
    private function errorReport($msg)
    {
        throw new \RuntimeException($msg);
    }
}
