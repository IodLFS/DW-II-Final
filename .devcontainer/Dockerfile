FROM mcr.microsoft.com/devcontainers/php:8.2-bullseye

# Instalar dependências do sistema e extensões PHP requeridas pelo Laravel
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get -y install --no-install-recommends \
       libzip-dev \
       unzip \
       libonig-dev \
       libcurl4-openssl-dev \
       libxml2-dev \
       apache2 \
    && docker-php-ext-install pdo pdo_mysql zip mbstring bcmath xml curl

# Configurar Apache para usar o PHP do container e servir PHP vanilla (TP1)
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i 's|<Directory /var/www/>|<Directory /workspaces/>|g' /etc/apache2/apache2.conf \
    && sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Instalar Node.js (opcional, mas útil se usarem Vite/Frontend no futuro)
RUN su vscode -c "source /usr/local/share/nvm/nvm.sh && nvm install lts/*"
