# Entorno de pruebas para GitHub Codespaces: PHP 8.3 + MariaDB
FROM php:8.3-cli-bookworm

RUN apt-get update \
 && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
      mariadb-server mariadb-client git unzip procps \
      libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
 && docker-php-ext-install pdo_mysql gd \
 && rm -rf /var/lib/apt/lists/*

# Permitir subir imágenes de hasta 10 MB
RUN printf "upload_max_filesize=10M\npost_max_size=12M\nmemory_limit=256M\ndate.timezone=America/Mexico_City\n" \
      > /usr/local/etc/php/conf.d/somos-mascoteros.ini
