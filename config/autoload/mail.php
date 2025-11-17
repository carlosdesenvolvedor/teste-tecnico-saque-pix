<?php

return [
    'default' => $_ENV['MAIL_MAILER'] ?? 'smtp',

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => $_ENV['MAIL_HOST'] ?? 'saque-pix-mailhog', // MailHog no Docker
            'port' => (int)($_ENV['MAIL_PORT'] ?? 1025), // Porta SMTP do MailHog
            'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? null,
            'username' => $_ENV['MAIL_USERNAME'] ?? '',
            'password' => $_ENV['MAIL_PASSWORD'] ?? '',
            'timeout' => 5,
        ],
    ],

    'from' => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com',
        'name' => $_ENV['MAIL_FROM_NAME'] ?? 'Hyperform',
    ],
];
