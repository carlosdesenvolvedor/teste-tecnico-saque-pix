<?php

declare(strict_types=1);

namespace App\Command;

use App\Job\ProcessoSaqueJob;
use App\Model\SaqueModel;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Context\ApplicationContext;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

#[Command]
class ProcessarSaquesAgendadosCommand extends HyperfCommand
{
    protected ?string $signature = 'withdraw:process-scheduled';

    protected string $description = 'Processa saques agendados que estão pendentes e já passaram da data/hora agendada';

    protected LoggerInterface $logger;
    protected DriverFactory $driverFactory;

    public function __construct()
    {
        parent::__construct();
        $container = ApplicationContext::getContainer();
        $loggerFactory = $container->get(LoggerFactory::class);
        $this->logger = $loggerFactory->get('cron');
        $this->driverFactory = $container->get(DriverFactory::class);
    }

    public function handle(): int
    {
        $this->info('Iniciando processamento de saques agendados...');
        $this->logger->info('Cron: Iniciando processamento de saques agendados');

        try {
            $agora = new \DateTime();
            
            // Busca saques agendados que estão pendentes e já passaram da data/hora agendada
            $saquesAgendados = SaqueModel::query()
                ->where('scheduled', true)
                ->where('status', SaqueModel::STATUS_PENDENTE)
                ->where('scheduled_for', '<=', $agora->format('Y-m-d H:i:s'))
                ->get();

            $total = $saquesAgendados->count();
            $this->info("Encontrados {$total} saque(s) agendado(s) para processar.");

            if ($total === 0) {
                $this->info('Nenhum saque agendado encontrado para processar.');
                $this->logger->info('Cron: Nenhum saque agendado encontrado para processar');
                return 0;
            }

            $processados = 0;
            foreach ($saquesAgendados as $saque) {
                try {
                    // Marca o saque como processando antes de enviar para a fila
                    $saque->status = SaqueModel::STATUS_PROCESSANDO;
                    $saque->save();

                    // Envia o job para processamento imediato (sem delay)
                    $job = new ProcessoSaqueJob($saque->id);
                    $this->driverFactory->get('default')->push($job, 0);

                    $this->info("Saque ID {$saque->id} enviado para processamento.");
                    $this->logger->info("Cron: Saque ID {$saque->id} enviado para processamento");
                    $processados++;
                } catch (\Throwable $e) {
                    $this->error("Erro ao processar saque ID {$saque->id}: " . $e->getMessage());
                    $this->logger->error("Cron: Erro ao processar saque ID {$saque->id}: " . $e->getMessage());
                    
                    // Reverte o status para pendente em caso de erro
                    $saque->status = SaqueModel::STATUS_PENDENTE;
                    $saque->save();
                }
            }

            $this->info("Processamento concluído. {$processados} de {$total} saque(s) enviado(s) para processamento.");
            $this->logger->info("Cron: Processamento concluído. {$processados} de {$total} saque(s) enviado(s) para processamento");

            return 0;
        } catch (\Throwable $e) {
            $this->error('Erro ao processar saques agendados: ' . $e->getMessage());
            $this->logger->error('Cron: Erro ao processar saques agendados: ' . $e->getMessage());
            return 1;
        }
    }
}

