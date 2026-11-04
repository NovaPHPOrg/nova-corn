<?php

declare(strict_types=1);

namespace nova\plugin\corn\schedule;

use nova\framework\core\Config;
use nova\framework\core\Context;
use RuntimeException;

final class TaskerConfig
{
    public const array MODES = ['internal', 'cli', 'http'];

    public static function mode(bool $reload = false): string
    {
        $config = $reload ? new Config() : Context::instance()->config();
        $mode = $config->get('corn.mode', 'internal');
        if (!in_array($mode, self::MODES, true)) {
            throw new RuntimeException('Invalid corn.mode configuration');
        }
        return $mode;
    }

    /** HTTP 调度专用 Token，独立于个人 Token。 */
    public static function token(bool $regenerate = false): string
    {
        $config = new Config();
        $token = $config->get('corn.token', '');
        if ($regenerate || !is_string($token) || strlen($token) < 32) {
            $token = bin2hex(random_bytes(24));
            $config->set('corn.token', $token);
        }
        // Config 析构时写盘，须在返回前完成持久化。
        unset($config);
        return $token;
    }
}
