<?php

declare(strict_types=1);

/*
 * Copyright (c) 2022-2025. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace nova\plugin\corn\schedule;

use nova\framework\cache\Cache;
use nova\framework\log\Logger;

use function nova\plugin\task\go;

class TaskerServer
{
    public const string SERVER_KEY = "tasker_server";

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
            }, 0);
        }
    }

    //停止任务
    public static function stop(): void
    {
        $cache = new Cache();
        $cache->set(self::SERVER_KEY, getmypid());
    }

}
