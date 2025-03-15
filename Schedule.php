<?php

declare(strict_types=1);

/*
 * Copyright (c) 2025. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace nova\plugin\corn;

use function nova\framework\isCli;

use nova\plugin\corn\schedule\TaskerServer;
use nova\plugin\task\Task;

class Schedule
{
    public static function register(): void
    {

        Task::register();
        if (isCli()) {
            return;
        }
        TaskerServer::start();
    }
}
