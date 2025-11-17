FROM hyperf/hyperf:8.2-alpine-v3.18-swoole

# Mude para o diretório de trabalho
WORKDIR /app

# Otimização de cache do Docker:
# 1. Copie apenas os arquivos do Composer primeiro.
COPY composer.json composer.json
COPY composer.lock composer.lock

# 3. Copie o restante do código da aplicação, respeitando o .dockerignore.
#    Isso garante que a pasta 'vendor' local não seja copiada.
COPY . . 

# 4. Otimize o autoloader.
RUN composer dump-autoload -o

# Exponha a porta que o Hyperf usa
EXPOSE 9501

# O entrypoint será definido no docker-compose.yml