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
        if ($cache->get(self::SERVER_KEY) === null) {
            Logger::info("No TaskerServer is running, start a new one");
            $cache->set(self::SERVER_KEY, getmypid(), 20);
            if (isWorkerman()) {
                \Workerman\Timer::add(10, function () use ($cache) {
                    $pid = getmypid();
                    $cache->set(self::SERVER_KEY, $pid, 15);
                    $ctx = Context::instance();
                    $ctx->cache = $cache;
                    try {
                        TaskerManager::run();
                    } finally {
                        // Timer 回调里没有请求生命周期，全局 Context 必须用完即毁：
                        // 否则它会带着旧的配置快照驻留，直到下个请求到来时才析构，
                        // 一旦期间任务改动过配置，析构写盘会把用户新保存的配置覆盖掉
                        global $context;
                        if ($context === $ctx) {
                            $context->destroy();
                            $context = null;
                        }
                        Logger::info("TaskerServer({$pid}) is running in the background");
                    }
                });
            } else {
                go("定时任务调度器", function () {

                    $cache = Context::instance()->cache;

                    do {
                        $pid = getmypid();
                        $cache->set(self::SERVER_KEY, $pid, 15);
                        TaskerManager::run();
                        sleep(10);
                        Logger::info("TaskerServer({$pid}) is running in the background");
                    } while ($cache->get(self::SERVER_KEY) === $pid);
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
