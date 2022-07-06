<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Util;

use Framework\Application;
use Framework\Contract\JsonAble;
use Monda\Utils\String\StringUtil;

/**
 * Class Result
 */
class Res implements JsonAble
{
    /**
     * @var bool 是否true false
     */
    private bool $success;

    /**
     * @var string 状态码
     */
    private string $code;

    /**
     * @var string 返回信息
     */
    private string $message;

    /**
     * @var array 对象
     */
    private array $data = [];

    /**
     * @var array 对象
     */
    private array $extra = [];

    /**
     * @var Pager 分页
     */
    private Pager $pager;

    private int $isUsePage = 0;

    /**
     * Result constructor.
     */
    private function __construct()
    {
    }

    /**
     * 转换字符串.
     */
    public function toJson(): string
    {
        $array = [
            'code' => $this->code,
            'succ' => $this->success,
            'msg' => $this->message,
        ];
        //是否分页
        if ($this->isUsePage === 1) {
            $array['data']['result'] = $this->data ?? [];
            $array['data']['pager'] = [
                'page' => optional($this->pager)->currentPage,
                'pageSize' => $this->pager->pageSize,
                'total' => $this->pager->total,
                'totalPage' => 0 != $this->pager->pageSize ? ceil($this->pager->total / $this->pager->pageSize) : 0,
            ];
        }
        if (isset($this->data) && $this->isUsePage !== 1) {
            $array['data'] = $this->data;
        }
        if (! empty($this->extra)) {
            $array['extra'] = $this->extra;
        }

        return StringUtil::jsonEncode($array);
    }

    /**
     * @return self
     *                设置成功
     */
    public static function ok(): self
    {
        $result = new self();
        $result->isSuccess(true)->code(ResultCode::SUCCESS['code'])->message(ResultCode::SUCCESS['message']);

        return $result;
    }

    /**
     * @return self
     *                错误
     */
    public static function error(): self
    {
        $result = new self();
        $result->isSuccess(false)->code(ResultCode::ERROR['code'])->message(ResultCode::ERROR['message']);

        return $result;
    }

    /**
     * @param  int  $total
     * @param  array  $data
     * @param  array  $extra
     * @return self
     *                      分页
     */
    public static function pager(int $total, array $data, array $extra = []): self
    {
        $pageParameterConfig = config('request.pageParameter', 'page');
        $pageSizeParameterConfig = config('request.pageSizeParameter', 'pageSize');
        $page = (int) Application::$request->get($pageParameterConfig, 1);
        $pageSize = (int) Application::$request->get($pageSizeParameterConfig, 10);
        $result = new self();
        $result->isSuccess(true)->code(ResultCode::SUCCESS['code'])->message(ResultCode::SUCCESS['message'])->data($data)->extra($extra)->setPager(new Pager($page, $pageSize, $total));

        return $result;
    }

    /**
     * @param  bool  $success
     * @return $this
     *               设置success
     */
    public function isSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @param  array  $data
     * @return $this
     *               设置数据
     */
    public function data(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param  string  $message
     * @return $this
     *               设置信息
     */
    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return $this
     *               设置分页
     */
    public function setPager(Pager $pager): self
    {
        $this->pager = $pager;
        $this->isUsePage = 1;

        return $this;
    }

    /**
     * @param  string  $code
     * @return $this
     *               设置状态码
     */
    public function code(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param $extra
     * @return $this
     *               设置额外数据
     */
    public function extra($extra): self
    {
        $this->extra = $extra;

        return $this;
    }
}
