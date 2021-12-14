<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\database\command;

use framework\command\AbstractCommand;
use framework\command\exception\CommandException;
use framework\command\Input;
use framework\command\MakeTraits;
use framework\database\command\makeEntity\AddPropertyVistor;
use framework\database\command\makeEntity\ClassVisitor;
use framework\database\command\makeEntity\Field;
use framework\database\HeroDB;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

/**
 * Class MakeEntityCommand
 * @package framework\database\command
 */
class MakeEntityCommand extends AbstractCommand
{
    use MakeTraits;

    //command 名称
    protected $name = 'make:entity';
    
    //command 描述
    protected $description = '数据库表实体entity生成';

    //command 定义
    protected $definition;

    private $connect = 'default';

    private $table = '';

    private $tableClassMap = [];

    private $pdo = null;

    /**
     * 匹配前缀
     *
     * @var string $prefix
     */
    private $prefix = '';

    /**
     * eg:  php artisan.php test
     * output: test.
     * @param Input|null $input
     */
    public function run(Input $input = null): void
    {
        echo '正在生成entity文件...' . PHP_EOL;
        $this->initMakeProperties($input);
        $this->initParams($input);
        $this->generateEntity();
        $this->csFix();
    }

    /**
     * 命令行参数
     * [[参数名称， 是否必填， 描述], ...].
     */
    public function optionDefinition(): array
    {
        return [
            ['-path', 'require', '以app_path()根目录开始,命名空间与路径对应 eg:app/entity'],
            ['-connect', '数据库连接, 默认default'],
            ['-table', '关联创建的数据表名,不传默认创建当前连接库下所有的表'],
            ['-class', '需要自定义了类名,不传默认以数据表名称,传此参数旧必须传递-table参数'],
            ['-prefix', '表名前缀匹配， 实体类名都会去除前缀eg: uc_user => User, uc_foo => Foo']
        ];
    }

    /**
     * 生成entity实体文件
     */
    private function generateEntity(): void
    {
        $this->pdo = HeroDB::connection($this->connect)->getPdo();
        if (empty($this->table)) {
            $tables = $this->pdo->query($this->getTableSql())->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            $tables = [$this->table];
        }
        foreach ($tables as $table) {
            $this->table = $table;
            $this->reSetClassName();
            $this->create();
            echo sprintf("完成'%s'文件\n", $this->getFullClassName());
        }
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    private function getTableSql(): string
    {
        if (! empty($this->prefix)) {
            return 'SHOW TABLES like \'' . $this->prefix . '%\'';
        }
        return 'SHOW TABLES';
    }

    /**
     * 重置className
     */
    private function reSetClassName(): void
    {
        $className = $this->tableClassMap[$this->table] ?? null;
        $this->className = $className ?: str_replace('_', '', ucwords(str_replace($this->prefix ?? '', '', $this->table), '_'));
        //关键字检测
        if ($this->iskeyWords($this->className)) {
            $this->className = $this->className . '_';
        }
    }

    /**
     * 关键字检测
     *
     * @param string $name
     * @return boolean
     */
    private function iskeyWords(string $name): bool
    {
        $keys = [
            '__halt_compiler', 'abstract', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class',
            'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif',
            'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends',
            'final', 'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements',
            'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'match',
            'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once',
            'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var',
            'while', 'xor', 'yield', 'yield', 'from', '__class__', '__dir__', '__file__', '__function__' . '__line__' .
            '__method__', '__namespace__', '__trait__'
        ];
        if (in_array(strtolower($name), $keys)) {
            return true;
        }
        return false;
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
        try {
            $columns = $this->pdo->query('SHOW FULL FIELDS FROM ' . $this->table)->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($columns as $column) {
                $fields[] = new Field($column);
            }
        } catch (\Exception $e) {
            throw new CommandException($e->getMessage());
        }
        $this->updateFile($fields);
    }

    /**
     * 更新文件代码
     * @param array $fields
     */
    private function updateFile(array $fields): void
    {
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $traverser = new NodeTraverser();
        $prettyPrinter = new Standard();

        $stmts = $parser->parse(file_get_contents($this->getFile()));
        $classVisitor = new ClassVisitor($fields, $this->table, $this->connect);
        $traverser->addVisitor($classVisitor);
        $newStmts = $traverser->traverse($stmts);

        //添加属性检查
        $traverser->removeVisitor($classVisitor);
        $addPropertyVisitor = new AddPropertyVistor($fields, $newStmts);
        $traverser->addVisitor($addPropertyVisitor);
        $newStmts = $traverser->traverse($newStmts);

        $newCode = $prettyPrinter->prettyPrintFile($newStmts);
        file_put_contents($this->getFile(), $newCode);
    }

    /**
     * 从模板获取代码
     */
    private function newFileFromStub(): void
    {
        $this->newByStub(function () {
            $stubFile = __DIR__ . '/makeEntity/entity.stub';
            $code = file_get_contents($stubFile);
            $code = str_replace(['{{namespace}}', '{{class}}'], [$this->namespace, $this->className], $code);
            return $code;
        });
    }

    /**
     * 初始化参数
     * @param Input $input
     */
    private function initParams(Input $input): void
    {
        $connect = $input->getOption('connect');
        if (! empty($connect)) {
            $this->connect = $connect;
        }
        $this->checkConnect();
        $this->table = $input->getOption('table');
        $this->tableClassMap[$this->table] = $this->className;
        $this->prefix = $input->getOption('prefix');
    }

    /**
     * 检查数据库连接配置
     * @return bool
     */
    private function checkConnect(): bool
    {
        $config = config('database') ?: [];
        if (! isset($config[$this->connect])) {
            throw  new CommandException(sprintf('当前连接"%s"没有配置', $this->connect));
        }
        return true;
    }
}
