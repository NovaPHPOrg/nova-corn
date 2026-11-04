<?php

declare(strict_types=1);

namespace nova\plugin\corn;

use nova\framework\core\Instance;
use nova\framework\http\Request;
use nova\framework\http\Response;

use function nova\framework\route;

use nova\framework\route\Route;
use nova\plugin\login\AdminPageInterface;
use nova\plugin\tpl\ViewResponse;

class CornTpl extends Instance implements AdminPageInterface
{
    public function registerRouter(string $model, string $controller): void
    {
        $default = route($model, $controller, 'init');
        Route::getInstance()
            ->get('/corn/list', $default)
            ->get('/corn/settings', $default);
    }

    public function route(ViewResponse $view, Request $request): ?Response
    {
        $templates = [
            '/corn/list' => 'list',
            '/corn/settings' => 'settings',
        ];
        $template = $templates[$request->getPath()] ?? null;
        if ($template === null) {
            return null;
        }

        return $view->asTpl(ROOT_PATH . DS . 'nova/plugin/corn/tpl/' . $template);
    }

    public function menu(): array
    {
        return [
            'title' => '定时任务',
            'icon' => 'schedule',
            'sub' => [
                [
                    'title' => '任务列表',
                    'icon' => 'list',
                    'url' => '/corn/list',
                    'pjax' => true,
                ],
                [
                    'title' => '调度设置',
                    'icon' => 'settings',
                    'url' => '/corn/settings',
                    'pjax' => true,
                ],
            ],
        ];
    }
}
