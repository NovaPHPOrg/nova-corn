<?php

declare(strict_types=1);

namespace nova\plugin\corn\controller;

use nova\framework\core\Config;
use nova\framework\core\Context;
use nova\framework\http\Response;
use nova\plugin\corn\schedule\TaskerConfig;
use nova\plugin\corn\schedule\TaskerManager;
use nova\plugin\corn\schedule\TaskerServer;
use nova\plugin\login\controller\BaseAPIController;

class Cron extends BaseAPIController
{
    public function list(): Response
    {
        $cache = Context::instance()->cache;
        $serverPid = $cache->get(TaskerServer::SERVER_KEY);

        return Response::asJson([
            'code' => 200,
            'count' => count(TaskerManager::list()),
            'data' => array_values(TaskerManager::list()),
            'server' => [
                'running' => $serverPid !== null,
                'pid' => $serverPid,
                'mode' => TaskerConfig::mode(),
                'last_tick' => $cache->get('tasker_last_tick'),
            ],
        ]);
    }

    public function config(): Response
    {
        $command = 'php ' . escapeshellarg(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'corn.php');
        $token = TaskerConfig::token();
        return Response::asJson([
            'code' => 200,
            'data' => [
                'mode' => TaskerConfig::mode(),
                'token' => $token,
                'trigger_url' => $this->request->getBasicAddress() . '/corn/api/run?token=' . $token,
                'cli_command' => $command,
                'cli_once_command' => $command . ' --once',
                'last_tick' => Context::instance()->cache->get('tasker_last_tick'),
            ],
        ]);
    }

    public function save(): Response
    {
        $mode = $this->request->post()['mode'] ?? null;
        if (!in_array($mode, TaskerConfig::MODES, true)) {
            return Response::asJson(['code' => 400, 'msg' => '请选择有效的调度方式'], 400);
        }
        if (TaskerConfig::mode() !== $mode) {
            $config = new Config();
            $config->set('corn.mode', $mode);
            // Config 析构时写盘，须在返回成功前完成持久化。
            unset($config);
            TaskerServer::stop();
        }
        return Response::asJson(['code' => 200, 'msg' => '调度设置已保存']);
    }

    public function resetToken(): Response
    {
        TaskerConfig::token(true);
        return Response::asJson(['code' => 200, 'msg' => '调度 Token 已重置，请更新外部触发地址']);
    }
}
