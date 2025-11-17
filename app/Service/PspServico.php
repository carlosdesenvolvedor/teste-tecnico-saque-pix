<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\SaqueModel;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

/**
 * Serviço que simula a comunicação com um Provedor de Serviços de Pagamento (PSP) para PIX.
 */
class PspServico
{
    protected LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('psp');
    }

    /**
     * Simula o envio de uma ordem de pagamento PIX para um PSP.
     *
     * @param SaqueModel $saque O modelo do saque a ser processado.
     * @return array Retorna ['sucesso' => bool, 'mensagem_erro' => string|null]
     */
    public function processarPagamentoPix(SaqueModel $saque): array
    {
        // Simulação: Saque falha se o valor (em centavos) for ímpar.
        if (((int)bcmul((string)$saque->amount, '100')) % 2 !== 0) {
            return ['sucesso' => false, 'mensagem_erro' => 'Falha simulada na comunicação com o PIX (Erro do PSP - Valor Ímpar).'];
        }

        return ['sucesso' => true, 'mensagem_erro' => null];
    }
}