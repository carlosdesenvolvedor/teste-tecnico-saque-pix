<?php

declare(strict_types=1);

namespace App\Service;

use FriendsOfHyperf\Mail\Contract\MailerInterface;
use Hyperf\View\RenderInterface;

class EmailServico
{
    public function __construct(
        protected MailerInterface $mailer,
        protected RenderInterface $render
    ) {
    }

    public function enviarNotificacao(string $destinatario, string $assunto, string $view, array $data): void
    {
        $html = $this->render->render($view, $data);
        // O primeiro e segundo parâmetros do `send` são para views, que já estamos renderizando manualmente.
        $this->mailer->send([], [], fn ($message) => $message->to($destinatario)->subject($assunto)->html($html));
    }
}
