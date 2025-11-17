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

use FriendsOfHyperf\Mail\Contract\Mailer;
use FriendsOfHyperf\Mail\Factory\MailerFactory;

return [
    // Binding explícito do Mailer para garantir que a injeção de dependências funcione
    Mailer::class => MailerFactory::class,
];
