<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\WithdrawRequest;
use App\Service\SaqueServico;
use Hyperf\HttpServer\Contract\ResponseInterface;

class SaqueController extends AbstractController
{
    public function __construct(private SaqueServico $saqueServico)
    {
    }

    public function withdraw(WithdrawRequest $request, ResponseInterface $response, string $accountId): ResponseInterface
    {
        $validatedData = $request->validated();
        $result = $this->saqueServico->criarSolicitacaoSaque($accountId, $validatedData);

        $statusCode = ($result['status'] === 'accepted') ? 202 : 400;

        return $response->json($result)->withStatus($statusCode);
    }
}