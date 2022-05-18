<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */

namespace framework\database;

use framework\database\filters\FilterableTrait;
use framework\server\HttpServer;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HeroModel.
 */
abstract class HeroModel extends Model
{
    use FilterableTrait;


    protected $perPage = 10;

    /**
     * 重写分页,当请求存在的时候
     * @return int
     */
    public function getPerPage(): int
    {
        if ($request = HttpServer::request()) {
            return (int)$request->getParameter('pageSize', $this->perPage);
        }
        return parent::getPerPage();
    }
}
