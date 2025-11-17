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

// Rotas da API conforme especificação do teste
// Agrupa todas as rotas relacionadas a uma conta
Router::addGroup('/account/{accountId}', function () {
    // Rota para consultar o saldo da conta
    Router::get('/balance', [ContaController::class, 'balance']);
    // Rota para solicitação de saque, conforme especificação: POST /account/{accountId}/balance/withdraw
    Router::post('/balance/withdraw', [SaqueController::class, 'withdraw']);
});