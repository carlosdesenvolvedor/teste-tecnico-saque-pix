-- Garante que estamos usando o banco de dados correto.
USE pix_withdraw_db;

-- Criação da tabela de contas
CREATE TABLE account (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    balance DECIMAL(15, 2) NOT NULL,
    email VARCHAR(255) NULL -- Adicionado para notificações por e-mail
);

-- Criação da tabela de saques
CREATE TABLE account_withdraw (
    id CHAR(36) PRIMARY KEY,
    account_id CHAR(36) NOT NULL,
    method VARCHAR(50) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    scheduled BOOLEAN DEFAULT FALSE,
    status VARCHAR(50) NOT NULL,
    scheduled_for DATETIME NULL,
    error_reason VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES account(id)
);

-- Criação da tabela com os dados do PIX para cada saque
CREATE TABLE account_withdraw_pix (
    account_withdraw_id CHAR(36) PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    `key` VARCHAR(255) NOT NULL,
    FOREIGN KEY (account_withdraw_id) REFERENCES account_withdraw(id)
);

-- Inserção de uma conta de exemplo para testes
INSERT INTO account (id, name, balance, email) VALUES
('123e4567-e89b-12d3-a456-426614174000', 'Conta de Exemplo', 1000.00, 'exemplo@test.com');
