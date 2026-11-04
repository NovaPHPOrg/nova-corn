<?php

declare(strict_types=1);

namespace nova\plugin\corn\controller;

use nova\framework\http\Response;
use nova\framework\route\Controller;
use nova\plugin\corn\schedule\TaskerConfig;
use nova\plugin\corn\schedule\TaskerManager;

/** 外部 HTTP 调度入口：不依赖登录，仅校验调度专用 Token。 */
class Trigger extends Controller
{
    public function run(): Response
    {
        $token = $this->request->get('token', '');
        if (!is_string($token) || !hash_equals(TaskerConfig::token(), $token)) {
            return Response::asJson(['code' => 403, 'msg' => '调度 Token 无效'], 403);
        }
        if (TaskerConfig::mode() !== 'http') {
            return Response::asJson(['code' => 409, 'msg' => '当前未启用外部 URL 调度'], 409);
        }
        ignore_user_abort(true);
        set_time_limit(0);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        if (!TaskerManager::run()) {
            return Response::asJson(['code' => 409, 'msg' => '任务列表正在扫描，请等待下一次触发'], 409);
        }
        return Response::asJson(['code' => 200, 'msg' => '任务列表扫描完成']);
    }
}
