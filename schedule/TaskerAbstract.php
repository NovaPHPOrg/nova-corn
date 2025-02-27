<?php
/*
 * Copyright (c) 2022-2025. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Package: extend\ankioTask\core
 * Class ATasker
 * Created By ankio.
 * Date : 2022/6/4
 * Time : 09:35
 * Description :
 */

namespace nova\plugin\corn\schedule;


use Throwable;

abstract class TaskerAbstract
{

    /**
     * 该任务最长的运行时间，单位秒，为0不限制
     * @return int
     */
    abstract public function getTimeOut(): int;

    /**
     * 任务被启动的时候
     * @return void
     */
    abstract public function onStart(): void;

    /**
     * 任务停止的时候
     * @return void
     */
    abstract public function onStop(): void;

    /**
     * 任务因为异常退出的时候
     * @param Throwable $e
     * @return void
     */
    abstract public function onAbort(Throwable $e): void;


}