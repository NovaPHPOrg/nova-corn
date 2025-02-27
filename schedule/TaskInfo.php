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
 * Package: library\task
 * Class TaskInfo
 * Created By ankio.
 * Date : 2022/11/19
 * Time : 17:11
 * Description :
 */

namespace nova\plugin\corn\schedule;

class TaskInfo
{
    public string $key = "";//任务ID
    public string $name = "";//任务名称
    public string $cron = '';
    public int $next = 0;//下次的执行时间
    public bool $loop = false;//是否循环
    public int $times = 0;//循环次数
    public $closure;//序列化的执行事件
}
