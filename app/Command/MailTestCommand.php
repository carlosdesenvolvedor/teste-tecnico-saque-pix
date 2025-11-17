<?php

declare(strict_types=1);

namespace App\Command;

use FriendsOfHyperf\Mail\Contract\MailerInterface;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

// #[Command] // Temporarily disabled to prevent startup DI errors.
class MailTestCommand extends HyperfCommand
{
    public function __construct(protected MailerInterface $mailer)
    {
        parent::__construct('mail:test');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Enviar e-mail de teste usando Mailhog.');
    }

    public function handle()
    {
        $this->info('📨 Enviando e-mail de teste...');

        $this->mailer->to('teste@example.com')
            ->subject('Email de Teste Hyperf + MailHog')
            ->text('Funcionando com sucesso!');

        $this->info('✔ Email enviado! Confira no MailHog: http://localhost:8025');
    }
}
