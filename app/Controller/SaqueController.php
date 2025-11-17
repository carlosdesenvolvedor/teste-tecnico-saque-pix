<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\WithdrawRequest;
use App\Service\SaqueServico;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Throwable;

class SaqueController extends AbstractController
{
    #[Inject]
    protected LoggerInterface $logger;

    public function __construct(private SaqueServico $saqueServico)
    {
    }

    public function withdraw(WithdrawRequest $request, string $accountId): ResponseInterface
    {
        try {
            $this->logger->info("Iniciando solicitação de saque para conta: {$accountId}", [
                'request_data' => $request->all()
            ]);

            $validatedData = $request->validated();
            $result = $this->saqueServico->criarSolicitacaoSaque($accountId, $validatedData);

            $statusCode = ($result['status'] === 'accepted') ? 202 : 400;

            $this->logger->info("Solicitação de saque processada", [
                'account_id' => $accountId,
                'status' => $result['status'],
                'status_code' => $statusCode
            ]);

            $response = $this->response->json($result);
            return $response->withStatus($statusCode);
        } catch (Throwable $e) {
            $this->logger->error("Erro ao processar solicitação de saque: " . $e->getMessage(), [
                'account_id' => $accountId,
                'trace' => $e->getTraceAsString()
            ]);

            $response = $this->response->json([
                'codigo' => 500,
                'mensagem' => 'Erro interno ao processar a solicitação de saque.',
                'erro' => $e->getMessage(),
            ]);
            return $response->withStatus(500);
        }
    }
}