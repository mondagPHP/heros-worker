<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\image;

use framework\http\Response;

/**
 * Interface CaptchaBuilderInterface.
 */
interface CaptchaBuilderInterface
{
    /**
     * 创建验证图片.
     * @return mixed
     */
    public function create();

    /**
     * 将验证码图片保存到指定路径.
     * @param  string $filename 物理路径
     * @param  int    $quality  清晰度
     * @return mixed
     */
    public function save(string $filename, int $quality): bool;

    /**
     * 获取验证码图片.
     * @param  int   $quality 清晰度
     * @return mixed
     */
    public function output(int $quality): Response;

    /**
     * 获取验证码内容.
     * @return mixed
     */
    public function getText(): string;
}
