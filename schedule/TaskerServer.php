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

use nova\framework\core\Context;
use nova\framework\core\Logger;

use function nova\framework\isWorkerman;
use function nova\plugin\task\go;

use Workerman\Timer;

class TaskerServer
{
    public const string SERVER_KEY = "tasker_server";

    /**
     * 启动任务扫描服务
     * @return void
     */
    public static function start(): void
    {
        $cache = Context::instance()->cache;
        $key = self::SERVER_KEY;
        if ($cache->get(self::SERVER_KEY) === null) {
            Logger::info("No TaskerServer is running, start a new one");
            $cache->set(self::SERVER_KEY, getmypid(), 20);
            if (isWorkerman()) {
                Timer::add(10, function () use ($key, $cache) {
                    $pid = getmypid();
                    $cache->set($key, $pid, 15);
                    Context::instance()->cache = $cache;
                    TaskerManager::run();
                    Logger::info("TaskerServer({$pid}) is running in the background");
                });
            } else {
                go(function () use ($key) {

                    $cache = Context::instance()->cache;

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
    }

    //停止任务
    public static function stop(): void
    {
        $cache = Context::instance()->cache;
        $cache->set(self::SERVER_KEY, getmypid());
    }

}
