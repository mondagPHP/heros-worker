<?php
declare(strict_types=1);
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\database\command;

use framework\command\AbstractCommand;
use framework\command\Input;
use framework\command\MakeTraits;
use framework\command\Manager;
use framework\file\FileUtils;

/**
 * 更新实体
 */
class UpdateEntityCommand extends AbstractCommand
{
    use MakeTraits;

    //command 名称
    protected $name = 'update:entity';

    //command 描述
    protected $description = '更新指定目录(path)下的实体文件';

    private $connect = 'default';

    private $table = '';

    private $tableClassMap = [];

    /**
     * @inheritDoc
     *
     * @param Input $input
     * @return void
     */
    public function run(Input $input = null): void
    {
        $this->path = $input->getOption('path');
        $files = $this->getDirFiles($this->getDir());

        self::$csFix = false;

        foreach ($files as $file) {
            $makeInput = $this->getMakeInput($file);
            if ($makeInput) {
                Manager::exec(new MakeEntityCommand(), $makeInput);
            }
        }
        self::$csFix = true;
        $this->csFix();
        echo '更新完成' . PHP_EOL;
    }

    public function optionDefinition(): array
    {
        return [
            ['-path', 'require', '以app_path()根目录开始,命名空间与路径对应 eg:app/entity'],
        ];
    }

    /**
     * 获取Input
     *
     * @param string $file
     * @return Input|null
     */
    private function getMakeInput(string $file): ?Input
    {
        $content = file_get_contents($file);
        $path = str_replace(FileUtils::sysPath(BASE_PATH . '/'), '', dirname($file));
        $connect = 'default';
        $class = pathinfo($file, PATHINFO_FILENAME);
        $table = '';

        if (preg_match('/' . $class . '\s*?extends\s*?HeroModel/', $content, $m1) === false) {
            return null;
        }
        if (preg_match('/\$connection\s*?=\s*?\'([a-zA-Z0-9_-]+)\'/m', $content, $m2) !== false) {
            if (isset($m2[1])) {
                $connect = $m2[1];
            }
        }
        if (preg_match('/\$table\s*?=\s*?\'([a-zA-Z0-9_-]+)\'/m', $content, $m3) !== false) {
            if (! isset($m3[1])) {
                return null;
            }
            $table = $m3[1];
        } else {
            return null;
        }
        return new Input([
            'path' => $path,
            'connect' => $connect,
            'table' => $table,
            'class' => $class
        ]);
    }

    /**
     * 获取path下所有文件
     *
     * @param string $dir
     * @return array
     */
    private function getDirFiles(string $dir): array
    {
        $files = [];

        if (! is_dir($dir)) {
            return $files;
        }

        $dirIter = new \DirectoryIterator($dir);
        foreach ($dirIter as $item) {
            if ($item->isDot()) {
                continue;
            }
            $path = $item->getPathname();
            if ($item->isDir()) {
                if (count(scandir($path)) !== 2) {
                    $files = array_merge($files, $this->getDirFiles($path));
                }
            } elseif ($item->isFile()) {
                $files[] = $path;
            }
        }
        return $files;
    }

    private function getDir()
    {
        return FileUtils::sysPath(BASE_PATH . '/' . $this->path);
    }
}
