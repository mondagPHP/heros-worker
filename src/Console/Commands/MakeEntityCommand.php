<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Console\Commands;

use Framework\Database\HeroDB;
use Monda\Utils\File\FileUtil;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeEntityCommand extends Command
{
    protected static $defaultName = 'make:entity';

    protected static $defaultDescription = '数据库表实体entity生成';

    protected string $className = '';

    protected string $namespace = 'App\Entity';

    private string $connect;

    private string $table;

    private string $prefix;

    private array $tableClassMap = [];

    private string $path;

    private \PDO $pdo;

    private string $entityDir;

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, '以app_path()根目录开始,命名空间与路径对应 eg:app/entity', '');
        $this->addOption('connect', null, InputOption::VALUE_OPTIONAL, '数据库连接, 默认default', 'default');
        $this->addOption('table', null, InputOption::VALUE_OPTIONAL, '关联创建的数据表名,不传默认创建当前连接库下所有的表', '');
        $this->addOption('prefix', null, InputOption::VALUE_OPTIONAL, '表名前缀匹配， 实体类名都会去除前缀eg: uc_user => User, uc_foo => Foo', '');
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->entityDir = base_path().'/app/Entity';
        $this->table = $input->getOption('table');
        $this->connect = $input->getOption('connect');
        $this->path = $input->getOption('path');
        $this->prefix = $input->getOption('prefix');
        if ($this->path) {
            $this->namespace = $this->namespace.'\\'.ucwords($this->path);
        }
        $this->pdo = HeroDB::connection($this->connect)->getPdo();
        $this->generateEntity();

        return self::SUCCESS;
    }

    /**
     * 根据stub创建文件
     *
     * @param  \Closure  $closure 返回code
     */
    protected function newByStub(\Closure $closure): void
    {
        $code = $closure();
        if (! is_string($code)) {
            throw new \RuntimeException('根据stub创建文件newByStub():参数闭包返回值必须是string');
        }
        file_put_contents($this->getFile(), $code);
    }

    protected function getFile(): string
    {
        FileUtil::makeFileDirs($this->entityDir.'/'.$this->path);

        return $this->entityDir.'/'.$this->path.'/'.sprintf('%s.php', $this->className);
    }

    /**
     * 是否存在class
     *
     * @return bool
     */
    protected function existClass(): bool
    {
        return class_exists($this->getFullClassName());
    }

    /**
     * @return string
     */
    protected function getFullClassName(): string
    {
        return $this->namespace.'\\'.$this->className;
    }

    /**
     * 生成entity实体文件
     */
    private function generateEntity(): void
    {
        if (! $this->table) {
            $tables = $this->pdo->query($this->getTableSql())->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            $tables = [$this->table];
        }
        foreach ($tables ?? [] as $table) {
            $this->table = $table;
            $this->reSetClassName();
            $this->create();
            echo sprintf("完成'%s'文件\n", $this->getFullClassName());
        }
    }

    /**
     * 创建单个文件
     */
    private function create(): void
    {
        if (! $this->existClass()) {
            $this->newFileFromStub();
        }
        $fields = [];
        $columns = $this->pdo->query('SHOW FULL FIELDS FROM '.$this->table)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            $fields[] = new Field($column);
        }
        $this->updateFile($fields);
    }

    /**
     * 更新文件代码
     *
     * @param  array  $fields
     */
    private function updateFile(array $fields): void
    {
        $traverser = new NodeTraverser();
        $stmts = (new ParserFactory())->create(ParserFactory::ONLY_PHP7)->parse(file_get_contents($this->getFile()));
        $classVisitor = new ClassVisitor($fields, $this->table, $this->connect);
        $traverser->addVisitor($classVisitor);
        $newStmts = $traverser->traverse($stmts);
        $traverser->removeVisitor($classVisitor);
        $addPropertyVisitor = new AddPropertyVistor($fields, $newStmts);
        $traverser->addVisitor($addPropertyVisitor);
        $newStmts = $traverser->traverse($newStmts);
        $newCode = (new Standard())->prettyPrintFile($newStmts);
        file_put_contents($this->getFile(), $newCode);
    }

    /**
     * 从模板获取代码
     */
    private function newFileFromStub(): void
    {
        $this->newByStub(function () {
            $stubFile = __DIR__.'/stub/entity.stub';
            $code = file_get_contents($stubFile);

            return str_replace(['{{namespace}}', '{{class}}'], [$this->namespace, $this->className], $code);
        });
    }

    /**
     * 重置className
     */
    private function reSetClassName(): void
    {
        $className = $this->tableClassMap[$this->table] ?? null;
        $this->className = $className ?: str_replace('_', '', ucwords(str_replace($this->prefix ?? '', '', $this->table), '_'));
        if ($this->iskeyWords($this->className)) {
            $this->className = $this->className.'_';
        }
    }

    /**
     * @return string
     */
    private function getTableSql(): string
    {
        if (! empty($this->prefix)) {
            return 'SHOW TABLES like \''.$this->prefix.'%\'';
        }

        return 'SHOW TABLES';
    }

    /**
     * 关键字检测
     *
     * @param  string  $name
     * @return bool
     */
    private function isKeyWords(string $name): bool
    {
        $keys = [
            '__halt_compiler', 'abstract', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class',
            'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif',
            'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends',
            'final', 'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements',
            'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'match',
            'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once',
            'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var',
            'while', 'xor', 'yield', 'yield', 'from', '__class__', '__dir__', '__file__', '__function__'.'__line__'.
            '__method__', '__namespace__', '__trait__',
        ];
        if (in_array(strtolower($name), $keys)) {
            return true;
        }

        return false;
    }
}
