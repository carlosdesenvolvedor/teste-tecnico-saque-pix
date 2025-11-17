<?php

declare(strict_types=1);

/**
 * Configurações específicas para o ambiente de teste.
 * Este arquivo desabilita listeners que não devem ser executados durante os testes.
 */
return [
    'listeners' => [
        // Remove o App\Listener\BootProcessListener::class para evitar chamadas de exit()
        // que interrompem a execução do PHPUnit.
        Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class,
        Hyperf\Command\Listener\FailToHandleListener::class,
    ],
];