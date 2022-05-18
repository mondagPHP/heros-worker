<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Util;

/**
 * Class Pager
 * @package Framework\Util
 */
class Pager
{
    /**
     * @var int 当前页
     */
    public int $currentPage;

    /**
     * @var int 分页数量
     */
    public int $pageSize;

    /**
     * @var int 总量
     */
    public int $total;

    /**
     * @var int 总页数
     */
    public int $totalPage;

    /**
     * Pager constructor.
     *
     * @param int $currentPage
     * @param int $pageSize
     * @param int $total
     * 分页
     */
    public function __construct(int $currentPage, int $pageSize, int $total)
    {
        $this->currentPage = $currentPage;
        $this->pageSize = $pageSize;
        $this->total = $total;
        $this->totalPage = 0 != $pageSize ? (int)ceil($total / $pageSize) : 0;
    }
}
