#!/bin/sh

# Garante que o script pare se algum comando falhar
set -e

# Em ambiente de desenvolvimento, limpa o cache do container para refletir novas alterações.
# Em produção, essa etapa é pulada para uma inicialização mais rápida.
if [ "${APP_ENV}" = "dev" ]; then
    echo "Ambiente de desenvolvimento detectado. Limpando cache do container..."
    rm -rf /app/runtime/container/*
fi

# Inicia a aplicação Hyperf
exec php /app/bin/hyperf.php start