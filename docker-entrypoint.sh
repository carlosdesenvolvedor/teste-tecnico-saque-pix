#!/bin/sh
set -e

# Verifica se o autoloader do Composer existe.
# Se não existir, significa que as dependências não foram instaladas.
if [ ! -f "vendor/autoload.php" ]; then
    echo "Arquivo 'vendor/autoload.php' não encontrado. Instalando dependências do Composer..."
    # Instala as dependências.
    composer install --no-dev --no-interaction --optimize-autoloader
else
    echo "Arquivo 'vendor/autoload.php' encontrado. Pulando instalação do Composer."
fi

# Mantém o processo principal em execução.
# O comando original (CMD do Dockerfile) será passado aqui.
exec "$@"