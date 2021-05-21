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
            ['-class', '需要自定义了类名,不传默认以数据表名称,传此参数旧必须传递-table参数']
        ];
    }

    /**
     * 生成entity实体文件
     */
    private function generateEntity(): void
    {
        $this->pdo = HeroDB::connection($this->connect)->getPdo();
        if (empty($this->table)) {
            $tables = $this->pdo->query('SHOW tables')->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            $tables = [$this->table];
        }
        foreach ($tables as $table) {
            $this->table = $table;
            $this->className = $this->tableClassMap[$this->table] ?? str_replace('_', '', ucwords($table, '_'));
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
        $traverser->addVisitor(new ClassVisitor($fields, $this->table));
        $newStmts = $traverser->traverse($stmts);

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
