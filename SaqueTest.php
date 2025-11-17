<?php

declare(strict_types=1);

namespace HyperfTest\Feature;

use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class SaqueTest extends HttpTestCase
{
    protected Client $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = make(Client::class);
    }

    public function testWithdrawSuccess()
    {
        $accountId = '123e4567-e89b-12d3-a456-426614174000';

        $response = $this->client->post(
            "/api/accounts/{$accountId}/balance/withdraw",
            [
                'method' => 'PIX',
                'pix' => [
                    'type' => 'email',
                    'key' => 'teste@email.com',
                ],
                'amount' => 100.50,
                'schedule' => null,
            ]
        );

        // Verifica se a resposta HTTP foi 202 Accepted
        $this->assertSame(202, $response['code']);

        // Verifica se a mensagem de sucesso está correta
        $this->assertSame('Saque solicitado com sucesso. O processamento ocorrerá em breve.', $response['data']['message']);

        // Verifica se o ID do saque (UUID) foi retornado
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response['data']['withdraw_id']);
    }
}