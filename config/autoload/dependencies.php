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
use FriendsOfHyperf\Mail\Contract\MailerInterface;
use FriendsOfHyperf\Mail\Mailer;

return [
    // Mapeamento explícito para que o contêiner de DI use a fábrica correta
    // para construir a MailerInterface. Isso resolve o problema de forma definitiva.
    MailerInterface::class => \FriendsOfHyperf\Mail\MailerFactory::class,
];
