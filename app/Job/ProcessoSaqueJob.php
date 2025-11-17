<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\ContaModel;
use App\Model\SaqueModel;
use App\Service\EmailServico;
use App\Service\PspServico;
use Carbon\Carbon;
use Hyperf\AsyncQueue\Job;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Job para processamento assíncrono do saque (imediato ou agendado).
 * Simula o serviço de backend que processa as filas, garantindo a atomicidade
 * da transação (saldo, status) e comunicação com o PSP.
 */
class ProcessoSaqueJob extends Job
{
    // O Job será re-tentado até 3 vezes em caso de exceções.
    public int $maxAttempts = 3;

    protected EmailServico $emailServico;
    protected PspServico $pspServico;
    protected LoggerInterface $logger;

    /**
     * @param string $withdrawId O ID do saque a ser processado.
     */
    public function __construct(protected string $withdrawId, ContainerInterface $container)
    {
        // Injeta as dependências necessárias a partir do container.
        $this->emailServico = $container->get(EmailServico::class);
        $this->pspServico = $container->get(PspServico::class);
        $this->logger = $container->get(LoggerFactory::class)->get('async-withdraw-processor');
    }

    public function handle(): void
    {
        $this->logger->info("Job: Iniciando processamento do Saque ID #{$this->withdrawId}");

        try {
            /** @var SaqueModel|null $withdraw */
            $withdraw = SaqueModel::query()->where('id', $this->withdrawId)
                ->with(['account', 'pix'])
                ->first();

            // 1. Validação: Garante que o saque existe e está pendente.
            if (!$withdraw || !in_array($withdraw->status, [SaqueModel::STATUS_PENDENTE, SaqueModel::STATUS_PROCESSANDO])) {
                $status = $withdraw?->status ?? 'N/A';
                $this->logger->warning("Job: Saque ID #{$this->withdrawId} não encontrado ou status incorreto ({$status}). Abortando.");
                return;
            }

            // Inicia a transação para garantir a atomicidade da operação de saldo.
            Db::beginTransaction();

            try {
                /** @var ContaModel|null $account */
                $account = $withdraw->account;

                // 2. Verificação de Saldo
                if (!$account || bccomp((string)$account->balance, (string)$withdraw->amount, 2) === -1) {
                    $this->setWithdrawFailed($withdraw, 'Saldo insuficiente no momento do processamento.');
                    $withdraw->save(); // Salva o status de falha
                    Db::commit(); // Comita a falha
                    return;
                }

                // 3. Dedução de Saldo
                $account->balance = bcsub((string)$account->balance, (string)$withdraw->amount, 2);
                $account->save();
                $this->logger->info("Job: Saldo R$ {$withdraw->amount} deduzido da conta #{$account->id}.");

                Db::commit();
            } catch (Throwable $e) {
                Db::rollBack();
                $this->logger->error("Job: Erro na transação de banco de dados para o Saque ID #{$this->withdrawId}. Erro: " . $e->getMessage());
                throw $e; // Lança para a fila tentar novamente.
            }

            // 4. Comunicação com o PSP (fora da transação de DB)
            $resultadoPsp = $this->pspServico->processarPagamentoPix($withdraw);

            // 5. Atualização de Status Pós-PSP e Notificação
            if ($resultadoPsp['sucesso']) {
                // Sucesso no PSP: Marca o saque como concluído e envia notificação.
                $withdraw->status = SaqueModel::STATUS_CONCLUIDO;
                $withdraw->error_reason = null;
                $this->logger->info("Job: Saque ID #{$this->withdrawId} concluído com sucesso via PSP.");
                $withdraw->save();
                $this->sendNotification($withdraw); // Envia notificação apenas em caso de sucesso
            } else {
                // Falha no PSP: Estorna o valor e atualiza o status.
                Db::transaction(function () use ($withdraw, $resultadoPsp) {
                    $account = $withdraw->account()->lockForUpdate()->first();
                    $this->setWithdrawFailed($withdraw, $resultadoPsp['mensagem_erro'] ?? 'Falha desconhecida no PSP.');
                    $this->reverseBalance($account, $withdraw->amount);
                    $withdraw->save();
                    $account->save();
                });
            }

        } catch (Throwable $e) {
            $this->logger->error("Job: Erro fatal ao processar Saque ID #{$this->withdrawId}. Erro: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Lançar a exceção novamente é crucial.
            // Isso informa ao worker da fila que o Job falhou e deve ser re-tentado
            // (até $maxAttempts) ou movido para a fila de falhas.
            // Não fazer isso faria o worker considerar o Job como bem-sucedido.
            throw $e;
        }
    }

    /**
     * Envia o email de notificação após o saque ser concluído.
     */
    protected function sendNotification(SaqueModel $withdraw): void
    {
        try {
            // O requisito especifica o envio para o e-mail da chave PIX.
            $destinatario = ($withdraw->pix && $withdraw->pix->type === 'email') ? $withdraw->pix->key : null;
            
            // Só prossegue se houver um destinatário de e-mail válido.
            if ($destinatario) {
                $dataHoraSaque = Carbon::parse($withdraw->updated_at)->format('d/m/Y H:i:s');
                // Garante que o valor é formatado corretamente
                $valorSacado = number_format((float)$withdraw->amount, 2, ',', '.');
                $tipoChave = $withdraw->pix->type ?? 'Desconhecido';
                $chavePix = $withdraw->pix->key ?? 'N/A';

                $this->emailServico->enviarNotificacao(
                    $destinatario,
                    'Saque PIX Concluído',
                    'emails.saque_concluido', // Template a ser criado
                    [
                        'dataHoraSaque' => $dataHoraSaque,
                        'valorSacado' => $valorSacado,
                        'tipoChave' => $tipoChave,
                        'chavePix' => $chavePix,
                    ]
                );
                $this->logger->info("Job: E-mail de notificação para o saque #{$this->withdrawId} enviado para '{$destinatario}'.");
            } else {
                $this->logger->info("Job: Envio de e-mail para o saque #{$this->withdrawId} não realizado (chave PIX não é do tipo e-mail).");
            }
        } catch (Throwable $e) {
            // A falha no envio do e-mail não deve reverter a transação do saque. Apenas registramos o erro.
            $this->logger->error("Job: Falha ao enviar e-mail para o saque #{$this->withdrawId}: " . $e->getMessage());
        }
    }

    /**
     * Define o status do saque como falho e registra o motivo do erro.
     */
    protected function setWithdrawFailed(SaqueModel $withdraw, string $reason): void
    {
        $withdraw->status = SaqueModel::STATUS_FALHOU;
        $withdraw->error_reason = $reason;
        $this->logger->warning("Saque ID #{$this->withdrawId} marcado como falha: {$reason}");
    }

    /**
     * Reverte o valor deduzido para a conta.
     */
    protected function reverseBalance(ContaModel $account, string $amount): void
    {
        $this->logger->info("Estornando valor R$ {$amount} para a conta #{$account->id} devido a falha no PSP.");
        $account->balance = bcadd((string)$account->balance, $amount, 2);
    }
}