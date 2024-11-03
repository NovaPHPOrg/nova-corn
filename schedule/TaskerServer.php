<?php
/*******************************************************************************
 * Copyright (c) 2022. CleanPHP. All Rights Reserved.
 ******************************************************************************/

namespace nova\plugin\corn\schedule;

use nova\framework\cache\Cache;
use nova\framework\log\Logger;
use nova\plugin\task\Task;
use function nova\framework\dump;
use function nova\plugin\task\go;


class TaskerServer
{

    const string SERVER_KEY = "tasker_server";

    /**
     * 启动任务扫描服务
     * @return void
     */
    public static function start(): void
    {

        $cache = new Cache();

        if ($cache->get(self::SERVER_KEY) === null) {
            $cache->set(self::SERVER_KEY, getmypid(), 20);
            go(function () {
                $key = self::SERVER_KEY;
                $cache = new Cache();
                do {
                    $pid = getmypid();
                    $cache->set($key, $pid, 15);
                    TaskerManager::run();
                    sleep(10);
                    Logger::info("TaskerServer({$pid}) is running in the background");
                } while ($cache->get($key) === $pid);
            },0);
        }
    }

    //停止任务
    public static function stop(): void
    {
        $cache = new Cache();
        $cache->set(self::SERVER_KEY, getmypid());
    }

}