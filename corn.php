<?php

declare(strict_types=1);

use nova\framework\core\Config;
use nova\framework\core\Context;
use nova\framework\core\Loader;
use nova\framework\core\Logger;
use nova\plugin\corn\CornManager;
use nova\plugin\corn\schedule\TaskerConfig;
use nova\plugin\corn\schedule\TaskerManager;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$options = array_slice($argv, 1);
if ($options === ['--help']) {
    echo "Usage: php corn.php [--once]\nWithout --once, scan every 10 seconds until stopped.\n";
    exit(0);
}
if ($options !== [] && $options !== ['--once']) {
    fwrite(STDERR, "Usage: php corn.php [--once]\n");
    exit(1);
}
if (!function_exists('pcntl_fork')) {
    fwrite(STDERR, "CLI scheduling requires the PHP pcntl extension (Linux/Unix).\n");
    exit(1);
}

set_time_limit(0);
$root = dirname(__DIR__, 3);
if (file_exists($root . '/vendor/autoload.php')) {
    require $root . '/vendor/autoload.php';
}
require $root . '/nova/framework/core/Loader.php';
$loader = new Loader();
require $root . '/nova/framework/helper.php';
require $root . '/nova/plugin/task/helper.php';

$running = true;
pcntl_async_signals(true);
pcntl_signal(SIGCHLD, SIG_IGN);
foreach ([SIGTERM, SIGINT] as $signal) {
    pcntl_signal($signal, static function () use (&$running): void {
        $running = false;
    });
}

$once = $options === ['--once'];
$exitCode = 0;
$started = false;
do {
    $startedAt = hrtime(true);
    try {
        // CLI 没有 HTTP Host，使用配置中的允许域名完成框架初始化。
        $config = new Config();
        $domains = $config->get('domain', ['0.0.0.0']);
        $_SERVER['HTTP_HOST'] = is_array($domains) && is_string($domains[0] ?? null) ? $domains[0] : 'localhost';
        unset($config);
        $context = new Context($loader);
        $context->init();
        if (TaskerConfig::mode() !== 'cli') {
            fwrite(STDERR, "CLI scheduling is disabled. Select CLI in scheduler settings.\n");
            $exitCode = $started && !$once ? 0 : 1;
            break;
        }
        CornManager::bootApplication();
        $scanned = TaskerManager::run();
        $started = true;
        echo date('Y-m-d H:i:s') . ($scanned ? " task list scanned\n" : " scan already running, skipped\n");
    } catch (Throwable $exception) {
        fwrite(STDERR, 'Scheduler failed: ' . $exception->getMessage() . "\n");
        if (isset($context)) {
            Logger::error($exception->getMessage(), $exception->getTrace());
        }
        $exitCode = 1;
        break;
    } finally {
        if (isset($context)) {
            $context->destroy();
            $context = null;
        }
    }
    if (!$once && $running) {
        $remaining = 10_000_000_000 - (hrtime(true) - $startedAt);
        if ($remaining > 0) {
            usleep((int)($remaining / 1000));
        }
    }
} while (!$once && $running);

exit($exitCode);
