<?php

declare(strict_types=1);

namespace App\Service;

use App\Job\ProcessoSaqueJob;
use App\Model\ContaModel;
use App\Model\DadosPixModel;
use App\Model\SaqueModel;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Contract\ContainerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class SaqueServico
{
    protected LoggerInterface $logger;

    public function __construct(
        protected DriverFactory $driverFactory,
        protected ContainerInterface $container, // Mantido para obter o Job com parâmetros
        protected PspServico $pspService,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->get('withdraw');
    }

    /**
     * Cria uma nova solicitação de saque, aplica regras de saldo e a envia para a fila assíncrona.
     */
    public function criarSolicitacaoSaque(string $accountId, array $data): array
    {
        $valor = (string)($data['amount'] ?? 0);
        $dataAgendamento = $data['schedule'] ?? null; // A validação já foi feita no FormRequest
        $agendado = !is_null($dataAgendamento);

        // Inicia a Transação para garantir atomicidade no registro e dedução de saldo
        Db::beginTransaction();

        try {
            /** @var ContaModel|null $account */
            $account = ContaModel::query()->where('id', $accountId)->first();

            if (!$account) {
                Db::rollBack();
                return ['status' => 'failed', 'mensagem' => 'Conta não encontrada.'];
            }

            // Para saques imediatos, fazemos uma verificação prévia de saldo.
            // A verificação final e a dedução ocorrerão de forma atômica dentro do Job.
            if (!$agendado && bccomp($account->balance, $valor, 2) === -1) {
                Db::rollBack();
                return ['status' => 'failed', 'mensagem' => 'Saldo insuficiente para a operação.'];
            }

            $idSaque = Uuid::uuid4()->toString();

            SaqueModel::create([
                'id' => $idSaque,
                'account_id' => $accountId,
                'method' => 'PIX',
                'amount' => $valor,
                'scheduled' => $agendado,
                'scheduled_for' => $dataAgendamento,
                'status' => SaqueModel::STATUS_PENDENTE, // Define o status inicial como pendente
            ]);

            DadosPixModel::create([
                'account_withdraw_id' => $idSaque,
                'type' => $data['pix']['type'],
                'key' => $data['pix']['key'],
            ]);

            Db::commit();

            // Para saques imediatos, envia para a fila de processamento
            // Para saques agendados, o cron job irá processá-los quando chegar a hora
            if (!$agendado) {
                // Cria o job diretamente com apenas o ID do saque (sem container para evitar problemas de serialização)
                $job = new ProcessoSaqueJob($idSaque);
                $this->driverFactory->get('default')->push($job, 0);
                $mensagem = "Saque enviado para processamento.";
            } else {
                // Saque agendado será processado pelo cron job
                $mensagem = "Saque agendado com sucesso para {$dataAgendamento}.";
            }

            return ['status' => 'accepted', 'id_saque' => $idSaque, 'mensagem' => $mensagem];

        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error("Erro na transação ao criar solicitação de saque: " . $e->getMessage());
            return ['status' => 'failed', 'mensagem' => 'Erro interno ao salvar a solicitação.'];
        }
    }
}