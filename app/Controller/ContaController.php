<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\ContaModel;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;
use Throwable;

class ContaController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    #[Inject]
    protected LoggerInterface $logger;

    /**
     * Consulta e retorna o saldo de uma conta específica.
     *
     * @param string $accountId O ID da conta a ser consultada.
     * @return ResponseInterface
     */
    public function balance(string $accountId): ResponseInterface
    {
        $this->logger->info('Iniciando consulta de saldo para a conta: ' . $accountId);

        try {
            /** @var ContaModel|null $account */
            $account = ContaModel::query()->find($accountId, ['id', 'balance']);

            if (!$account) {
                $this->logger->warning('Conta não encontrada: ' . $accountId);
                return $this->response->json([
                    'codigo' => 404,
                    'mensagem' => 'Conta não encontrada.'
                ])->withStatus(404);
            }

            $this->logger->info('Saldo consultado com sucesso para a conta: ' . $accountId);
            return $this->response->json([
                'account_id' => $account->id,
                'balance' => $account->balance,
            ]);
        } catch (Throwable $e) {
            $this->logger->error('Erro ao consultar saldo: ' . $e->getMessage());
            return $this->response->json([
                'codigo' => 500,
                'mensagem' => 'Erro interno do servidor.',
                'erro' => $e->getMessage(),
            ])->withStatus(500);
        }
    }
}