# PHP-FPM 8.1 mit benötigten Extensions + Xdebug
FROM php:8.1-fpm

# Systemabhängigkeiten
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-install intl mbstring zip opcache \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

# Composer installieren
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
