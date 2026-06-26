<?php

declare(strict_types=1);

namespace nova\plugin\corn;

use nova\framework\core\StaticRegister;

use function nova\framework\isCli;

use nova\framework\route\RouteTrait;
use nova\plugin\corn\schedule\TaskerServer;
use nova\plugin\login\AdminPage;
use nova\plugin\login\route\Permission;

class CornManager extends StaticRegister
{
    use RouteTrait;

    public function __construct()
    {
        $this->controllerNamespace = 'nova\\plugin\\corn\\controller\\';
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        $this->get('/corn/api/list', $this->map('cron', 'list'));
    }

    public static function registerInfo(): void
    {

        Permission::getInstance()->registerPermissions('定时任务', 'corn_manage', [
            'ANY /corn*',
        ]);

        self::getInstance()->bindPrefixDispatch('/corn');
        AdminPage::bind(CornTpl::getInstance());
        if (isCli()) {
            return;
        }
        TaskerServer::start();
    }
}
