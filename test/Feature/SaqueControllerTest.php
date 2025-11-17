<?php

declare(strict_types=1);

namespace HyperfTest\Feature;

use App\Model\ContaModel;
use Hyperf\Testing\Concerns\RefreshDatabase;
use Hyperf\Testing\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @covers \App\Controller\SaqueController
 */
class SaqueControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSuccessfulImmediateWithdrawal(): void
    {
        $accountId = Uuid::uuid4()->toString();
        ContaModel::create([
            'id' => $accountId,
            'name' => 'Test Account',
            'email' => 'test@example.com',
            'balance' => '1000.00',
        ]);

        $response = $this->post("/account/{$accountId}/balance/withdraw", [
            'method' => 'PIX',
            'pix' => [
                'type' => 'email',
                'key' => 'test@example.com',
            ],
            'amount' => 150.75,
        ]);

        $response->assertStatus(202)
            ->assertJson([
                'codigo' => 0,
                'mensagem' => 'Saque enviado para processamento.',
            ]);
    }

    public function testSuccessfulScheduledWithdrawal(): void
    {
        $accountId = Uuid::uuid4()->toString();
        ContaModel::create([
            'id' => $accountId,
            'name' => 'Test Account',
            'email' => 'test@example.com',
            'balance' => '1000.00',
        ]);

        $schedule = date('Y-m-d H:i:s', strtotime('+1 day'));
        $response = $this->post("/account/{$accountId}/balance/withdraw", [
            'method' => 'PIX',
            'pix' => [
                'type' => 'email',
                'key' => 'test@example.com',
            ],
            'amount' => 150.75,
            'schedule' => $schedule,
        ]);

        $response->assertStatus(202)
            ->assertJson([
                'codigo' => 0,
                'mensagem' => "Saque agendado com sucesso para {$schedule}.",
            ]);
    }

    public function testWithdrawalWithInsufficientFunds(): void
    {
        $accountId = Uuid::uuid4()->toString();
        ContaModel::create([
            'id' => $accountId,
            'name' => 'Test Account',
            'email' => 'test@example.com',
            'balance' => '100.00',
        ]);

        $response = $this->post("/account/{$accountId}/balance/withdraw", [
            'method' => 'PIX',
            'pix' => [
                'type' => 'email',
                'key' => 'test@example.com',
            ],
            'amount' => 150.75,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'codigo' => 422,
                'mensagem' => 'Saldo insuficiente para a operação.',
            ]);
    }

    public function testWithdrawalWithInvalidScheduleDateInThePast(): void
    {
        $accountId = Uuid::uuid4()->toString();
        ContaModel::create([
            'id' => $accountId,
            'name' => 'Test Account',
            'email' => 'test@example.com',
            'balance' => '1000.00',
        ]);

        $response = $this->post("/account/{$accountId}/balance/withdraw", [
            'method' => 'PIX',
            'pix' => [
                'type' => 'email',
                'key' => 'test@example.com',
            ],
            'amount' => 150.75,
            'schedule' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'codigo' => 400,
                'mensagem' => 'Não é permitido agendar um saque para uma data no passado.',
            ]);
    }

    public function testWithdrawalWithInvalidScheduleDateMoreThan7DaysInTheFuture(): void
    {
        $accountId = Uuid::uuid4()->toString();
        ContaModel::create([
            'id' => $accountId,
            'name' => 'Test Account',
            'email' => 'test@example.com',
            'balance' => '1000.00',
        ]);

        $response = $this->post("/account/{$accountId}/balance/withdraw", [
            'method' => 'PIX',
            'pix' => [
                'type' => 'email',
                'key' => 'test@example.com',
            ],
            'amount' => 150.75,
            'schedule' => date('Y-m-d H:i:s', strtotime('+8 days')),
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'codigo' => 400,
                'mensagem' => 'Não é permitido agendar um saque para mais de 7 dias no futuro.',
            ]);
    }

    public function testWithdrawalWithInvalidPixKeyType(): void
    {
        $accountId = Uuid::uuid4()->toString();
        ContaModel::create([
            'id' => $accountId,
            'name' => 'Test Account',
            'email' => 'test@example.com',
            'balance' => '1000.00',
        ]);

        $response = $this->post("/account/{$accountId}/balance/withdraw", [
            'method' => 'PIX',
            'pix' => [
                'type' => 'cpf',
                'key' => '12345678901',
            ],
            'amount' => 150.75,
        ]);

        $response->assertStatus(422);
    }
}
