<?php

declare(strict_types=1);

namespace App\Service;

use FriendsOfHyperf\Mail\Contract\Mailer;

class EmailServico
{
    public function __construct(
        protected Mailer $mailer
    ) {
    }

    public function enviarNotificacao(string $destinatario, string $assunto, string $view, array $data): void
    {
        // Extrai os dados necessários
        $dataHoraSaque = $data['dataHoraSaque'] ?? 'N/A';
        $valorSacado = $data['valorSacado'] ?? 'N/A';
        $tipoChave = $data['tipoChave'] ?? 'N/A';
        $chavePix = $data['chavePix'] ?? 'N/A';

        // Template HTML do email conforme requisito: data/hora, valor e dados do PIX
        $html = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h1 style='color: #2c3e50;'>Saque PIX Concluído</h1>
                <p>Seu saque foi processado com sucesso!</p>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h2 style='color: #27ae60; margin-top: 0;'>Detalhes do Saque</h2>
                    <p><strong>Data e Hora:</strong> {$dataHoraSaque}</p>
                    <p><strong>Valor Sacado:</strong> R$ {$valorSacado}</p>
                </div>
                
                <div style='background-color: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h2 style='color: #2980b9; margin-top: 0;'>Dados do PIX</h2>
                    <p><strong>Tipo de Chave:</strong> {$tipoChave}</p>
                    <p><strong>Chave PIX:</strong> {$chavePix}</p>
                </div>
                
                <p style='color: #7f8c8d; font-size: 12px; margin-top: 30px;'>
                    Este é um email automático, por favor não responda.
                </p>
            </div>
        </body>
        </html>";

        // Envia o email
        $this->mailer->send([], [], fn ($message) => $message->to($destinatario)->subject($assunto)->html($html));
    }
}
