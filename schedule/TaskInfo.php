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

use Closure;

/**
 * Task information data transfer object
 */
class TaskInfo
{
    /**
     * @param string              $key     Task ID
     * @param string              $name    Task name
     * @param string              $cron    Cron expression
     * @param int                 $next    Next execution time (timestamp)
     * @param bool                $loop    Whether to loop
     * @param int                 $times   Number of iterations
     * @param TaskerAbstract|null $closure Task execution closure
     */
    public function __construct(
        public string $key = "",
        public string $name = "",
        public string $cron = "",
        public int $next = 0,
        public bool $loop = false,
        public int $times = 0,
        public ?TaskerAbstract $closure = null
    ) {
    }
}
