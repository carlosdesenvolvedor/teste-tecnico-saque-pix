#!/bin/sh

# Garante que o script pare se algum comando falhar
set -e

# Limpa o cache de DI do Hyperf para evitar problemas em desenvolvimento
echo "Limpando cache do container..."
rm -rf /app/runtime/container/*

# Inicia a aplicação Hyperf
exec php /app/bin/hyperf.php start