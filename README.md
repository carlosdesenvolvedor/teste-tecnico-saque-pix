# Plataforma de Saque PIX

Este projeto é uma implementação de uma API para solicitação de saques via PIX, desenvolvida com o framework Hyperf 3. A arquitetura foi projetada com foco em performance, escalabilidade e observabilidade.

## Tecnologias e Decisões de Arquitetura

- **PHP 8.2 / Hyperf 3.1**: Framework de alta performance baseado em corrotinas, ideal para aplicações I/O-bound como microserviços.
- **Docker & Docker Compose**: O ambiente é totalmente containerizado, garantindo consistência entre desenvolvimento e produção e facilitando o setup.
- **MySQL 8**: Banco de dados relacional para persistência dos dados de contas e saques.
- **Redis**: Utilizado como driver para o `AsyncQueue` do Hyperf. O processamento dos saques é feito de forma assíncrona em uma fila, o que melhora a performance da API (respostas rápidas) e a resiliência do sistema.
- **MailHog**: Servidor de e-mail local para capturar e visualizar as notificações enviadas durante o desenvolvimento, sem a necessidade de um servidor SMTP real.
- **Fluentd**: Coletor de logs. Todos os logs da aplicação (erros, informações, etc.) são enviados para o Fluentd, centralizando a observabilidade e permitindo que os logs sejam facilmente encaminhados para outras ferramentas (ex: Elasticsearch, Loki) em um ambiente de produção.

## Como Executar

### Requisitos
- Docker
- Docker Compose

### Instalação

1. **Clone o repositório:**
   ```bash
   git clone <url-do-repositorio>
   cd <nome-do-projeto>
   ```

2. **Copie o arquivo de ambiente:**
   ```bash
   cp .env.example .env
   ```

3. **Suba os contêineres:**
   O comando a seguir irá construir a imagem da aplicação, baixar as dependências do Composer e iniciar todos os serviços.
   ```bash
   docker-compose up -d --build
   ```

### Endpoints Úteis

- **API**: `http://localhost:9502`
- **MailHog (Web UI)**: `http://localhost:8025` - Visualize os emails enviados aqui
- **Banco de Dados (via host)**: `localhost:3308` (porta 3308 para evitar conflito com MySQL local)

## Estrutura da API

### Consultar Saldo

- **Endpoint**: `GET /account/{accountId}/balance`
- **Descrição**: Retorna o saldo disponível de uma conta
- **Resposta**:
  ```json
  {
    "account_id": "123e4567-e89b-12d3-a456-426614174000",
    "balance": "1000.00"
  }
  ```

### Realizar Saque

- **Endpoint**: `POST /account/{accountId}/balance/withdraw`
- **Descrição**: Cria uma solicitação de saque, que pode ser imediata ou agendada.
- **Body**:
  ```json
  {
    "method": "PIX",
    "pix": {
      "type": "email",
      "key": "destinatario@email.com"
    },
    "amount": 150.75,
    "schedule": null
  }
  ```
- **Resposta (Sucesso - 202 Accepted)**:
  ```json
  {
    "status": "accepted",
    "id_saque": "uuid-do-saque",
    "mensagem": "Saque enviado para processamento."
  }
  ```
- **Resposta (Agendado - 202 Accepted)**:
  ```json
  {
    "status": "accepted",
    "id_saque": "uuid-do-saque",
    "mensagem": "Saque agendado com sucesso para 2026-01-01 15:00."
  }
  ```

## Regras de Negócio Implementadas

✅ **Validações de Saque**:
- Não é permitido sacar valor maior que o saldo disponível
- O saldo não pode ficar negativo
- Saques agendados não podem ser no passado
- Saques agendados não podem ser para mais de 7 dias no futuro
- Apenas método PIX com chave tipo email é suportado (extensível para outros tipos)

✅ **Processamento**:
- Saques imediatos são processados via fila assíncrona (Redis)
- Saques agendados são processados via cron job (executa a cada minuto)
- Transações atômicas garantem consistência do saldo
- Retry automático em caso de falhas (até 3 tentativas)

✅ **Notificações**:
- Email enviado automaticamente após saque concluído
- Email contém: data/hora, valor sacado e dados do PIX
- Emails podem ser visualizados no MailHog (http://localhost:8025)

## Processamento de Saques Agendados

O sistema utiliza um **cron job** que executa a cada minuto para verificar e processar saques agendados que já passaram da data/hora agendada. O cron job:

1. Busca saques com `scheduled = true` e `status = 'pendente'`
2. Filtra apenas os que `scheduled_for <= agora`
3. Marca como `processando` e envia para a fila de processamento
4. O Job assíncrono processa o saque (deduz saldo, comunica com PSP, envia email)

## Decisões de Arquitetura

### Por que usar fila assíncrona?
- **Performance**: A API responde imediatamente (202 Accepted) sem esperar o processamento
- **Resiliência**: Falhas temporárias são tratadas com retry automático
- **Escalabilidade**: Múltiplos workers podem processar jobs em paralelo

### Por que usar cron para saques agendados?
- **Conformidade**: Atende ao requisito explícito de usar cron
- **Confiabilidade**: Não depende de delays na fila que podem ser perdidos em reinicializações
- **Rastreabilidade**: Saques agendados ficam no banco até serem processados pelo cron

### Por que usar transações atômicas?
- **Consistência**: Garante que saldo e status do saque são atualizados juntos
- **Segurança**: Evita condições de corrida em operações concorrentes
- **Integridade**: Rollback automático em caso de erro

# Requirements

 - PHP >= 8.1
 - Any of the following network engines
   - Swoole PHP extension >= 5.0，with `swoole.use_shortname` set to `Off` in your `php.ini`
   - Swow PHP extension >= 1.3
 - JSON PHP extension
 - Pcntl PHP extension
 - OpenSSL PHP extension （If you need to use the HTTPS）
 - PDO PHP extension （If you need to use the MySQL Client）
 - Redis PHP extension （If you need to use the Redis Client）
 - Protobuf PHP extension （If you need to use the gRPC Server or Client）

# Installation using Composer

The easiest way to create a new Hyperf project is to use [Composer](https://getcomposer.org/). If you don't have it already installed, then please install as per [the documentation](https://getcomposer.org/download/).

To create your new Hyperf project:

```bash
composer create-project hyperf/hyperf-skeleton path/to/install
```

If your development environment is based on Docker you can use the official Composer image to create a new Hyperf project:

```bash
docker run --rm -it -v $(pwd):/app composer create-project --ignore-platform-reqs hyperf/hyperf-skeleton path/to/install
```

# Getting started

Once installed, you can run the server immediately using the command below.

```bash
cd path/to/install
php bin/hyperf.php start
```

Or if in a Docker based environment you can use the `docker-compose.yml` provided by the template:

```bash
cd path/to/install
docker-compose up
```

This will start the cli-server on port `9501`, and bind it to all network interfaces. You can then visit the site at `http://localhost:9501/` which will bring up Hyperf default home page.

## Hints

- A nice tip is to rename `hyperf-skeleton` of files like `composer.json` and `docker-compose.yml` to your actual project name.
- Take a look at `config/routes.php` and `app/Controller/IndexController.php` to see an example of a HTTP entrypoint.

**Remember:** you can always replace the contents of this README.md file to something that fits your project description.
