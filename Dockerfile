FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev zip curl libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html