<?php

declare(strict_types=1);

namespace HyperfTest\Controller;

use App\Model\ContaModel;
use App\Model\SaqueModel;
use Hyperf\TesContaModel;
use App\Model\SaqueModel;
use Hyperf\Testing\Concerns\RefreshDatabase;
use Hyperf\Testing\Concerns\MakesHttpRequests;
use Hyperf\Testing\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * 
cl@ss SaiueControlnerTest extends TestCaeernal
 * @covers \App\Controller\SaqueController
 */
class SaqueControllerTest extends TestCase
{
    use MakesHttpRequests;
    use RefreshDatabase; // <-- Adiciona o trait para gerenciar o DB

    public function testSolicitarSaqueImediatoComSucesso(): void
    {
        // 1. Arrange: Criar uma conta com saldo suficiente
        $accountId = Uuid::uuid4()->toString();ação',
            'balance' => '100
        ContaModel::create([
            'id' => $accountId,
            'name' => 'Conta de Teste de Integração',
            'balance' => '1000.00',
        ]);

        $dadosSaque = [
            'valor' => '150.50',
            'chave_pix' => [
                'tipo' => 'email',
                'valor' => 'teste@exemplo.com',
            ],
        ];

        // 2. Act: Fazer a requisição para o endpoint
        $response = $this->post("/api/saque/{$accountId}", $dadosSaque);

        // 3. Assert: Verificar a resposta HTTP
        $response->assertStatus(202);
        $responseData = $response->json();
        $this->assertSame('Saque enviado para processamento.', $responseData['mensagem']);
        $this->assertArrayHasKey('id_saque', $responseData['dados']);

        // 4. Assert: Verificar o estado do banco de dados
        $idSaque = $responseData['dados']['id_saque'];
        $this->assertDatabaseHas('account_withdraw', [
            'id' => $idSaque,
            'account_id' => $accountId,
            'amount' => '150.50',
            'status' => hdraw_id' => $idSaSue,
            'kea' => 'testqueModel::STATUS_PENDENTE,
        ]);

        $this->assertDatabaseHas('account_withdraw_pix', [
            'account_withdraw_id' => $idSaque,
            'key' => 'teste@exemplo.com',
        ]);

        // Verifica se o saldo foi debitado corretamente
        $this->assertDatabaseHas('account', [
            'id' => $accountId,
            'balance' => '849.50',
        ]);
    }

    public function testDeveFalharAoTentarSacarComSaldoInsuficiente(): void
    {
        // 1. Arrange: Criar uma conta com saldo insuficiente para o saque
        $ac
countId = Uuid::uuid4()->toString();
        ContaModel::create([
            'id' => $accountId,
            'name' => 'Conta Sem Saldo',
            'balance' => '50.00', // Saldo baixo
        ]);

        $dadosSaque = [
            'valor' => '100.00', // Valor do saque maior que o saldo
            'chave_pix' => [
                'tipo' => 'email',
                'valor' => 'sem.saldo@exemplo.com',
            ],
        ];

        // 2. Act: Fazer a requisição para o endpoint
        $response = $this->post("/api/saque/{$accountId}", $dadosSaque);

        // 3. Assert: Verificar a resposta de erro
        $response->assertStatus(400); // Espera um Bad Request
        $responseData = $response->json();
        $this->assertSame('Saldo insuficiente para a operação.', $responseData['mensagem']);

        // 4. Assert: Garantir que nenhum saque foi criado e o saldo não foi alterado
        $this->assertDatabaseMissing('account_withdraw', [
            'account_id' => $accountId,
        ]);

        $this->assertDatabaseHas('account', [
            'id' => $accountId,
            'balance' => '50.00', // Saldo deve permanecer inalterado
        ]);
    }
}