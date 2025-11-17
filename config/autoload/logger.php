<?php

declare(strict_types=1);
use Monolog\Handler\StreamHandler;
use Monolog\Level;

return [
    'default' => [
        'handlers' => [
            [
                'class' => StreamHandler::class,
                'constructor' => [
                    'stream' => 'php://stdout',
                    'level' => Level::Debug,
                ],
                'formatter' => [
                    'class' => \Monolog\Formatter\LineFormatter::class,
                ],
            ],
        ],
    ],
];