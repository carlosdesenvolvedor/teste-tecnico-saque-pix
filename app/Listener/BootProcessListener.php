<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Redis\RedisFactory;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

#[Listener]
class BootProcessListener implements ListenerInterface
{
    private LoggerInterface $logger;

    /**
     * Injetamos o LoggerFactory para obter o logger padrão que escreve no stdout.
     */
    public function __construct(
        private ContainerInterface $container,
        LoggerFactory $loggerFactory
    )
    {
        $this->logger = $loggerFactory->get('default');
    }

    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $this->checkService('Redis', function () {
            // CORREÇÃO: Obter uma conexão diretamente da factory para garantir a disponibilidade no boot.
            $this->container->get(RedisFactory::class)->get('default')->ping();
        });

        $this->checkService('Database', function () {
            $resolver = $this->container->get(ConnectionResolverInterface::class);
            $resolver->connection()->select('SELECT 1');
        });
    }

    private function checkService(string $serviceName, \Closure $closure): void
    {
        $maxAttempts = 30;
        for ($i = 1; $i <= $maxAttempts; ++$i) {
            try {
                $closure();
                $this->logger->info(sprintf('[BootProcess] %s is ready.', $serviceName));
                return;
            } catch (\Throwable $e) {
                $this->logger->warning(sprintf('[BootProcess] Waiting for %s... Attempt %d/%d. Error: %s', $serviceName, $i, $maxAttempts, $e->getMessage()));
                sleep(2);
            }
        }

        $this->logger->error(sprintf('[BootProcess] %s is not ready after %d attempts. Exiting.', $serviceName, $maxAttempts));
        exit(1);
    }
}