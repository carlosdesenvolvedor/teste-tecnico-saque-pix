<?php

declare(strict_types=1);

return [
    'default' => [
        // CRÍTICO: Este 'host' deve corresponder ao nome do serviço Redis no seu docker-compose.yml
        'host' => $_ENV['REDIS_HOST'] ?? 'saque-pix-redis',
        'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
        'auth' => $_ENV['REDIS_AUTH'] ?? null,
        'db' => (int) ($_ENV['REDIS_DB'] ?? 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 5.0,
            'wait_timeout' => 3.0,
            'heartbeat' => (float) ($_ENV['REDIS_HEARTBEAT'] ?? 60),
            'max_idle_time' => (float) ($_ENV['REDIS_MAX_IDLE_TIME'] ?? 60),
        ],
        // Usado pelo AsyncQueue
        'cluster' => [
            'enable' => (bool) ($_ENV['REDIS_CLUSTER_ENABLE'] ?? false),
            'name' => null,
            'seeds' => [],
        ],
    ],
];
