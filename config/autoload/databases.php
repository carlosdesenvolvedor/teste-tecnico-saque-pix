<?php

declare(strict_types=1);

// Este arquivo usa valores hardcoded para garantir que o Docker inicie 
// a conexão com o banco de dados antes da inicialização completa do framework.
return [
    'default' => [
        // A partir da linha 7: Usando valores estáticos do .env (DB_HOST=hyperf-mysql, etc.)
        'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'saque-pix-mysql',
        'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
        'database' => $_ENV['DB_DATABASE'] ?? 'pix_withdraw_db',
        'username' => $_ENV['DB_USERNAME'] ?? 'user',
        'password' => $_ENV['DB_PASSWORD'] ?? 'secret',
        
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => 60.0,
        ],
        'cache' => [
            'handler' => Hyperf\ModelCache\Handler\RedisHandler::class,
            'cache_key' => '{prefix}:m:%s:cache:%s', 
            'prefix' => 'default',
            'ttl' => 3600,
            'empty_model_ttl' => 600,
            'use_default_value' => true,
        ],
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
                'uses' => '',
                'table_mapping' => [],
            ],
        ],
    ],
];