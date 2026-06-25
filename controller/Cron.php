<?php

declare(strict_types=1);

namespace nova\plugin\corn\controller;

use nova\framework\core\Context;
use nova\framework\http\Response;
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
            ],
        ]);
    }
}
