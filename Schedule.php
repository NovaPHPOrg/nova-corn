<?php

namespace nova\plugin\corn;

use nova\plugin\corn\schedule\TaskerServer;
use nova\plugin\task\Task;

class Schedule
{
    static function register(): void
    {
        Task::register();
        TaskerServer::start();
    }
}