<?php

declare(strict_types=1);

namespace nova\plugin\corn;

use nova\framework\App;
use nova\framework\core\StaticRegister;
use nova\framework\event\EventManager;
use nova\framework\exception\ErrorHandler;

use function nova\framework\isCli;

use nova\framework\route\RouteTrait;
use nova\plugin\corn\schedule\TaskerServer;
use nova\plugin\login\AdminPage;
use nova\plugin\login\route\Permission;
use RuntimeException;

class CornManager extends StaticRegister
{
    use RouteTrait;

    public function __construct()
    {
        $this->controllerNamespace = 'nova\\plugin\\corn\\controller\\';
        $this->registerRoutes();
    }

    public static function bootApplication(): void
    {
        ErrorHandler::register();
        EventManager::register();
        $app = App::getInstance();
        $start = [$app, 'onFrameworkStart'];
        if (!is_callable($start)) {
            throw new RuntimeException('CLI 调度需要应用提供公开的 onFrameworkStart() 启动钩子。');
        }
        $start();
        EventManager::trigger('framework.start', $app);
    }

    private function registerRoutes(): void
    {
        $this->get('/corn/api/list', $this->map('cron', 'list'));
        $this->get('/corn/api/config', $this->map('cron', 'config'));
        $this->post('/corn/api/config', $this->map('cron', 'save'));
        $this->post('/corn/api/token', $this->map('cron', 'resetToken'));
        $this->get('/corn/api/run', $this->map('trigger', 'run'));
    }

    public static function registerInfo(): void
    {

        Permission::getInstance()->registerPermissions('定时任务', 'corn_manage', [
            'ANY /corn/list',
            'ANY /corn/settings',
            'ANY /corn/api/list',
            'ANY /corn/api/config',
            'ANY /corn/api/token',
        ]);

        self::getInstance()->bindPrefixDispatch('/corn');
        AdminPage::bind(CornTpl::getInstance());
        if (isCli()) {
            return;
        }
        TaskerServer::start();
    }
}
