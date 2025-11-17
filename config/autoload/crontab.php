<?php

declare(strict_types=1);

return [
    'enable' => true,
    'crontab' => [
        // Processa saques agendados a cada minuto
        // Verifica se há saques agendados que já passaram da data/hora agendada
        [
            'name' => 'processar-saques-agendados',
            'rule' => '* * * * *', // Executa a cada minuto
            'callback' => [\App\Command\ProcessarSaquesAgendadosCommand::class, 'handle'],
            'memo' => 'Processa saques agendados pendentes',
        ],
    ],
];

