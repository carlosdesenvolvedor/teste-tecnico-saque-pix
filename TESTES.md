# Testes e Verificações do Projeto

## ✅ Checklist de Funcionalidades

### 1. Estrutura do Banco de Dados
- [x] Tabela `account` com campos: id, name, balance, email
- [x] Tabela `account_withdraw` com campos: id, account_id, method, amount, scheduled, scheduled_for, status, error_reason
- [x] Tabela `account_withdraw_pix` com campos: account_withdraw_id, type, key
- [x] Timestamps desabilitados na tabela `account` (não possui created_at/updated_at)

### 2. Endpoints da API
- [x] `GET /account/{accountId}/balance` - Consulta saldo
- [x] `POST /account/{accountId}/balance/withdraw` - Realiza saque

### 3. Regras de Negócio Implementadas

#### Validações de Saque
- [x] Não permite sacar valor maior que o saldo disponível
- [x] Saldo não pode ficar negativo
- [x] Saques agendados não podem ser no passado
- [x] Saques agendados não podem ser para mais de 7 dias no futuro
- [x] Aceita formato de schedule: `Y-m-d H:i` ou `Y-m-d H:i:s`
- [x] Apenas método PIX com chave tipo email é suportado (extensível)

#### Processamento
- [x] Saques imediatos processados via fila assíncrona (Redis)
- [x] Saques agendados processados via cron job (executa a cada minuto)
- [x] Transações atômicas garantem consistência
- [x] Retry automático em caso de falhas (até 3 tentativas)
- [x] Status do saque atualizado corretamente (pendente → processando → concluido/falhou)

#### Notificações
- [x] Email enviado após saque concluído
- [x] Email contém: data/hora do saque, valor sacado, dados do PIX
- [x] Email visualizável no MailHog (http://localhost:8025)

### 4. Processamento de Saques Agendados
- [x] Cron job configurado (`config/autoload/crontab.php`)
- [x] Executa a cada minuto
- [x] Busca saques agendados pendentes
- [x] Filtra apenas os que já passaram da data/hora agendada
- [x] Marca como processando e envia para fila

### 5. Tratamento de Erros
- [x] Saldo insuficiente no momento do processamento
- [x] Falha na comunicação com PSP
- [x] Estorno automático em caso de falha no PSP
- [x] Logs detalhados para observabilidade

## 🧪 Testes Manuais Recomendados

### Teste 1: Saque Imediato com Sucesso
```bash
POST http://localhost:9502/account/123e4567-e89b-12d3-a456-426614174000/balance/withdraw
{
    "method": "PIX",
    "amount": "150.00",
    "pix": {
        "type": "email",
        "key": "destinatario.feliz@email.com"
    },
    "schedule": null
}
```
**Esperado**: Status 202, saque processado, saldo deduzido, email enviado

### Teste 2: Saque Agendado
```bash
POST http://localhost:9502/account/123e4567-e89b-12d3-a456-426614174000/balance/withdraw
{
    "method": "PIX",
    "amount": "100.00",
    "pix": {
        "type": "email",
        "key": "teste@email.com"
    },
    "schedule": "2025-11-17 18:00"
}
```
**Esperado**: Status 202, saque agendado, processado pelo cron quando chegar a hora

### Teste 3: Saldo Insuficiente
```bash
POST http://localhost:9502/account/123e4567-e89b-12d3-a456-426614174000/balance/withdraw
{
    "method": "PIX",
    "amount": "99999.00",
    "pix": {
        "type": "email",
        "key": "teste@email.com"
    },
    "schedule": null
}
```
**Esperado**: Status 400, mensagem de saldo insuficiente

### Teste 4: Validação de Schedule (Passado)
```bash
POST http://localhost:9502/account/123e4567-e89b-12d3-a456-426614174000/balance/withdraw
{
    "method": "PIX",
    "amount": "50.00",
    "pix": {
        "type": "email",
        "key": "teste@email.com"
    },
    "schedule": "2020-01-01 10:00"
}
```
**Esperado**: Status 422, erro de validação

### Teste 5: Validação de Schedule (Mais de 7 dias)
```bash
POST http://localhost:9502/account/123e4567-e89b-12d3-a456-426614174000/balance/withdraw
{
    "method": "PIX",
    "amount": "50.00",
    "pix": {
        "type": "email",
        "key": "teste@email.com"
    },
    "schedule": "2025-11-25 10:00"
}
```
**Esperado**: Status 422, erro de validação

### Teste 6: Consulta de Saldo
```bash
GET http://localhost:9502/account/123e4567-e89b-12d3-a456-426614174000/balance
```
**Esperado**: Status 200, retorna account_id e balance

## 🔍 Verificações Adicionais

### Docker
- [x] Todos os serviços no docker-compose.yml
- [x] Dependências configuradas corretamente
- [x] Healthchecks configurados
- [x] Volumes para persistência

### Observabilidade
- [x] Logs estruturados
- [x] Fluentd configurado
- [x] Logs de erro detalhados

### Segurança
- [x] Validação de entrada (FormRequest)
- [x] Transações atômicas
- [x] Lock de registros em operações críticas
- [x] Tratamento de exceções

### Escalabilidade
- [x] Processamento assíncrono
- [x] Fila Redis para jobs
- [x] Múltiplos workers
- [x] Sem estado compartilhado (stateless)

