<?php

declare(strict_types=1);

/*
 * Copyright (c) 2022-2025. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Package: extend\ankioTask\core
 * Class cycle
 * Created By ankio.
 * Date : 2022/5/10
 * Time : 19:29
 * Description :
 */

namespace nova\plugin\corn\schedule;

class TaskerTime
{
    /**
     * 指定多少秒之后执行（实际只能精确到分钟）
     * @param  int    $seconds
     * @return string
     */
    public static function after(int $seconds): string
    {
        $time = time() + $seconds;

        return
            (int)date('i', $time) . ' ' .
            (int)date('H', $time) . ' ' .
            (int)date('d', $time) . ' ' .
            (int)date('m', $time) . ' *';
    }

    /**
     * 每天【{@link $hour}时{@link $mintue}分】执行任务
     * @param         $hour   int 小时
     * @param         $minute int 分钟
     * @return string
     */
    public static function day(int $hour, int $minute): string
    {
        return "$minute $hour * * *";
    }

    /**
     * 每隔【{@link $day}天的{@link $hour}时{@link $mintue}分】执行任务
     * @param         $day    int 天数
     * @param         $hour   int 时间
     * @param         $minute int 分钟
     * @return string
     */
    public static function nDay(int $day, int $hour, int $minute): string
    {
        return "$minute $hour */$day * *";
    }

    /**
     * 每天每隔【{@link $hour}时的第{@link $mintue}分钟】执行任务
     * @param  int    $hour   小时
     * @param         $minute int 分钟
     * @return string
     */
    public static function nHour(int $hour, int $minute): string
    {
        return "$minute */$hour * * *";
    }

    /**
     * 每小时的【第{@link $mintue}分钟】执行任务
     * @param         $minute int 分钟
     * @return string
     */
    public static function hour(int $minute): string
    {
        return "$minute */1 * * *";
    }

    /**
     * 【每隔{@link $mintue}分钟】执行任务
     * @param         $minute int 分钟
     * @return string
     */
    public static function nMinute(int $minute): string
    {
        if ($minute === 0) {
            return "";
        }
        return "*/$minute * * * *";
    }

}
