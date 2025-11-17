#!/bin/sh
set -e

# 1. Instala as dependências do Composer, garantindo que o vendor/ esteja sempre correto.
if [ "${APP_ENV}" = "dev" ]; then
    echo "Instalando dependências de desenvolvimento..."
    composer install --no-interaction --optimize-autoloader
else
    echo "Instalando dependências de produção..."
    composer install --no-dev --no-interaction --optimize-autoloader
fi

# 2. Em ambiente de desenvolvimento, limpa o cache do contêiner para refletir novas alterações.
if [ "${APP_ENV}" = "dev" ]; then
    echo "Ambiente de desenvolvimento detectado. Limpando cache do container..."
    rm -rf /app/runtime/container/*
fi

# 3. Inicia a aplicação Hyperf. Este é o processo principal do contêiner.
exec php /app/bin/hyperf.php start