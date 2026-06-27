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
use nova\plugin\corn\schedule\Cron\CronExpression;

use function nova\plugin\task\__serialize;
use function nova\plugin\task\go;

use nova\plugin\task\TaskLogger;

use Throwable;

/**
 * Class Tasker
 * @package extend\net_ankio_tasker\core
 * Date: 2020/12/23 23:46
 * Author: ankio
 * Description: 定时任务管理器
 */
class TaskerManager
{
    public const string TASK_LIST = "tasker_list";
    public const int MAX_CONCURRENT = 5;

    /**
     * 清空所有定时任务
     * @return void
     */
    public static function clean(): void
    {
        Context::instance()->cache->delete(self::TASK_LIST);
    }

    /**
     * 判断是否存在指定的定时任务
     * @param       $key
     * @return bool
     */
    public static function has($key): bool
    {
        $list = self::list();
        /**
         * @var $value TaskInfo
         */
        foreach ($list as $value) {
            if ($key === $value->key || $key === $value->name) {
                return true;
            }
        }
        return false;
    }

    /**
     * 删除指定ID的定时任务
     * @param       $key
     * @return void
     */
    public static function del($key): void
    {
        $list = self::list();
        /**
         * @var $value TaskInfo
         */
        $new = [];
        foreach ($list as $value) {
            if ($key !== $value->key && $key !== $value->name) {
                $new[] = $value;
            }
        }
        Context::instance()->cache->set(self::TASK_LIST, $new);
    }

    /**
     * 添加一个定时任务，与linux定时任务语法完全一致
     * @param  string         $cron           定时任务时间包，使用{@link TaskerTime}来指定或手写cron字符串（不含秒数位，不支持问号）
     * @param  TaskerAbstract $taskerAbstract 需要运行的定时任务，需要继承{@link TaskerAbstract}类并实现{@link TaskerAbstract::onStart()}方法
     * @param  string         $name           定时任务名称
     * @param  int            $times          定时任务的执行次数，当times=-1的时候为循环任务
     *                                        返回定时任务ID
     * @return string
     */
    public static function add(string $cron, TaskerAbstract $taskerAbstract, string $name, int $times = 1): string
    {
        if (empty($name) || TaskerManager::has($name)) {
            return '';
        }
        $task = new TaskInfo();
        $task->name = $name;
        $task->cron = $cron;
        $task->times = $times;
        $task->loop = $times == -1;
        $task->key = uniqid("task_");

        if (!empty($cron)) {
            $next = CronExpression::factory($cron)->getNextRunDate()->getTimestamp();
        } else {
            $next = time() + 10;
        }

        $task->next = $next;
        $task->closure = $taskerAbstract;
        $list = self::list();
        $list[] = $task;

        Context::instance()->cache->set(self::TASK_LIST, $list);
        if (Context::instance()->isDebug()) {
            Logger::info("Tasker 添加定时任务：$name => " . get_class($taskerAbstract));
            Logger::info("Tasker 初次添加后，执行时间为：" . date("Y-m-d H:i:s", $task->next));
        }
        return $task->key;
    }

    /**
     * 执行一次遍历数据库
     * @return void
     */
    public static function run(): void
    {

        $data = self::list();
        if (Context::instance()->isDebug()) {
            Logger::info("task list", $data);
        }
        $cache = Context::instance()->cache;
        $running = count(TaskLogger::running())  - 1;
        $max = 6;
        /**
         * @var $value TaskInfo
         */
        foreach ($data as $k => $value) {
            //次序=0
            if ($value->times === 0) {
                Logger::debug("Tasker 该ID ({$value->name})[{$value->key}] 的定时任务执行完毕");
                unset($data[$k]);
                $cache->delete($value->key);
            } elseif ($value->next <= time()) {

                if ($running >= $max) {
                    $value->next = $value->next + 60;
                    Logger::debug("Tasker 任务目前总数过多，为了避免高负载，将该任务（{$value->name}）延迟1分钟：".date("Y-m-d H:i:s", $value->next));
                    continue;
                }

                if (!empty($value->cron)) {
                    $time = CronExpression::factory($value->cron)->getNextRunDate()->getTimestamp();
                    $value->next = $time;
                    $value->times--;
                    Logger::debug("Tasker 执行完成后，下次执行时间为：" . date("Y-m-d H:i:s", $time));
                } else {
                    $value->times = 0;
                    Logger::debug("Tasker 执行完成后，由于没有Cron表达式，直接结束");
                }

                /**
                 * @var TaskerAbstract $task
                 */
                $task = $value->closure;
                $timeout = $task->getTimeOut();

                if ($cache->get($value->key) !== null) {
                    Logger::debug("Tasker 该ID ({$value->name})[{$value->key}] 的定时任务正在执行中");
                    continue;
                }
                $cache->set($value->key, 1);

                $key = $value->key;
                $taskName = $value->name;
                go("定时任务：{$taskName}", function () use ($task, $key) {
                    $cache = Context::instance()->cache;
                    try {
                        Context::instance()->isDebug() && Logger::info("Tasker 异步执行：" . __serialize($task));
                        $task->onStart();
                    } catch (Throwable $exception) {
                        $cache->delete($key);
                        $task->onAbort($exception);
                        Logger::error($exception->getMessage(), $exception->getTrace());
                        throw $exception; // 重抛让 go() 标记任务失败，异常不可吞
                    } finally {
                        $cache->delete($key);
                        Context::instance()->isDebug() && Logger::info("Tasker 异步执行结束：");
                        $task->onStop();
                    }

                }, $timeout);
            }
        }
        Context::instance()->cache->set(self::TASK_LIST, $data);

    }

    /**
     * 获取执行时间
     * @param      $key
     * @return int
     */
    private static function getTimes($key): int
    {
        $task = self::get($key);
        if (!$task) {
            return 1 - $task->times;
        }
        return 0;
    }

    /**
     * 获取指定的定时任务
     * @param                $key
     * @return TaskInfo|null
     */
    public static function get($key): ?TaskInfo
    {
        $list = self::list();
        /**
         * @var $value TaskInfo
         */
        foreach ($list as $value) {
            if ($key === $value->key) {
                return $value;
            }
        }
        return null;
    }

    public static function getByName($name): ?TaskInfo
    {
        $list = self::list();
        /**
         * @var $value TaskInfo
         */
        foreach ($list as $value) {
            if ($name === $value->name) {
                return $value;
            }
        }
        return null;
    }

    /**
     * 获取定时任务列表
     * @return array
     */
    public static function list(): array
    {
        return Context::instance()->cache->get(self::TASK_LIST, []) ?: [];
    }

}
