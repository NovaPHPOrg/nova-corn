<?php

namespace nova\plugin\corn;

use nova\plugin\corn\schedule\TaskerServer;

class Schedule
{
    static function register(): void
    {
        TaskerServer::start();
    }
}