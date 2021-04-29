<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\util;

/**
 * Class Time.
 */
class Time
{
    /**
     * @param $time
     * @return false|string
     *                      格式化时间
     */
    public static function pretty($time)
    {
        $return = '';
        if (! is_numeric($time)) {
            $time = strtotime($time);
        }
        $htime = date('H:i', $time);
        $dif = abs(time() - $time);
        if ($dif < 10) {
            $return = '刚刚';
        } elseif ($dif < 3600) {
            $return = floor($dif / 60) . '分钟前';
        } elseif ($dif < 10800) {
            $return = floor($dif / 3600) . '小时前';
        } elseif (date('Y-m-d', $time) == date('Y-m-d')) {
            $return = '今天 ' . $htime;
        } elseif (date('Y-m-d', $time) == date('Y-m-d', strtotime('-1 day'))) {
            $return = '昨天 ' . $htime;
        } elseif (date('Y-m-d', $time) == date('Y-m-d', strtotime('-2 day'))) {
            $return = '前天 ' . $htime;
        } elseif (date('Y', $time) == date('Y')) {
            $return = date('m-d H:i', $time);
        } else {
            $return = date('Y-m-d H:i', $time);
        }
        return $return;
    }
}
