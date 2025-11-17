<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;

return [
    'app_name' => $_ENV['APP_NAME'] ?? 'skeleton',
    'app_env' => $_ENV['APP_ENV'] ?? 'dev',
    'scan_cacheable' => (bool) ($_ENV['SCAN_CACHEABLE'] ?? false),
    StdoutLoggerInterface::class => [
        'log_level' => [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ],
    ],
    // A configuração de 'annotations' deve estar aqui para ser carregada como array.
    'annotations' => [
        'scan' => [
            'paths' => [
                BASE_PATH . '/app',
            ],
            'ignore_annotations' => [
                'mixin',
            ],
        ],
    ],
    'provider' => [
        // Adiciona o ConfigProvider para carregar as variáveis de ambiente para $_ENV
        \Hyperf\Config\ConfigProvider::class,
        \FriendsOfHyperf\Mail\ConfigProvider::class,
        \Hyperf\Crontab\ConfigProvider::class,
    ],
];
