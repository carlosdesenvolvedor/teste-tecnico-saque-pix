<?php

declare(strict_types=1);

use App\Controller\ContaController;
use Hyperf\HttpServer\Router\Router;
use App\Controller\SaqueController;

// Rota de teste padrão e favicon
Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::get('/favicon.ico', function () {
    return '';
});

// Agrupa as rotas da API sob o prefixo /api
Router::addGroup('/api', function () {
    // Agrupa todas as rotas relacionadas a uma conta
    Router::addGroup('/accounts/{accountId}', function () {
        // Rota para consultar o saldo da conta
        Router::get('/balance', [ContaController::class, 'balance']);
        // Rota para solicitação de saque, alinhada à especificação do teste
        Router::post('/balance/withdraw', [SaqueController::class, 'withdraw']);
    });
});