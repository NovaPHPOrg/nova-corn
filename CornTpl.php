<?php

declare(strict_types=1);

namespace nova\plugin\corn;

use nova\framework\core\Instance;
use nova\framework\http\Request;
use nova\framework\http\Response;
use nova\plugin\login\AdminPageInterface;
use nova\plugin\tpl\ViewResponse;

class CornTpl extends Instance implements AdminPageInterface
{
    public function registerRouter(string $model, string $controller): void
    {
        $default = \nova\framework\route($model, $controller, 'init');
        \nova\framework\route\Route::getInstance()
            ->get('/corn/list', $default);
    }

    public function route(ViewResponse $view, Request $request): ?Response
    {
        if ($request->getPath() !== '/corn/list') {
            return null;
        }

        return $view->asTpl(ROOT_PATH . DS . 'nova/plugin/corn/tpl/list');
    }

    public function menu(): array
    {
        return [
            'title' => '定时任务',
            'icon' => 'schedule',
            'url' => '/corn/list',
            'pjax' => true,
        ];
    }
}
