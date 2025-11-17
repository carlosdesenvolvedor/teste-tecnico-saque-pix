<?php

declare(strict_types=1);

namespace HyperfTest\Service;

use App\Model\saque_model;
use App\Service\psp_servico;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Testing\TestCase;
use Mockery;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @internal
 * @covers \App\Service\psp_servico
 */
class PspServicoTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testProcessarPagamentoPixComSucessoParaValorPar()
    {
        // Arrange: Prepara o ambiente de teste
        $container = Mockery::mock(ContainerInterface::class);
        $logger = Mockery::mock(LoggerInterface::class);
        $loggerFactory = Mockery::mock(LoggerFactory::class);
        $loggerFactory->shouldReceive('get')->with('psp')->andReturn($logger);

        // Cria um mock do saque_model com um valor par (10.00)
        // CORREÇÃO: Usar makePartial() para permitir que a atribuição de propriedades
        // funcione como em um objeto real, sem que o Mockery intercepte.
        $saqueMock = Mockery::mock(saque_model::class)->makePartial();
        $saqueMock->amount = '10.00';

        // Instancia o serviço com as dependências mockadas
        $servico = new psp_servico($loggerFactory);

        // Act: Executa o método a ser testado
        $resultado = $servico->processar_pagamento_pix($saqueMock);

        // Assert: Verifica se o resultado é o esperado
        $this->assertTrue($resultado['sucesso']);
        $this->assertNull($resultado['mensagem_erro']);
    }

    public function testProcessarPagamentoPixComFalhaParaValorImpar()
    {
        // Arrange: Prepara o ambiente de teste
        $container = Mockery::mock(ContainerInterface::class);
        $logger = Mockery::mock(LoggerInterface::class);
        $loggerFactory = Mockery::mock(LoggerFactory::class);
        $loggerFactory->shouldReceive('get')->with('psp')->andReturn($logger);

        // Cria um mock do saque_model com um valor ímpar (10.01)
        // CORREÇÃO: Usar makePartial() para permitir que a atribuição de propriedades
        // funcione como em um objeto real, sem que o Mockery intercepte.
        $saqueMock = Mockery::mock(saque_model::class)->makePartial();
        $saqueMock->amount = '10.01';

        // Instancia o serviço
        $servico = new psp_servico($loggerFactory);

        // Act: Executa o método
        $resultado = $servico->processar_pagamento_pix($saqueMock);

        // Assert: Verifica se o resultado é o esperado
        $this->assertFalse($resultado['sucesso']);
        $this->assertEquals(
            'Falha simulada na comunicação com o PIX (Erro do PSP - Valor Ímpar).',
            $resultado['mensagem_erro']
        );
    }
}