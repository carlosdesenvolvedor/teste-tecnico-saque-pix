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
- **MailHog (Web UI)**: `http://localhost:8025`
- **Banco de Dados (via host)**: `localhost:3306`

## Estrutura da API

### Realizar Saque

- **Endpoint**: `POST /api/accounts/{accountId}/balance/withdraw`
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
