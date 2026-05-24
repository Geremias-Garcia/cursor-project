FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    libpq-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

COPY backend/composer.json backend/composer.lock* ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist || true

COPY backend/ .

RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

CMD ["php-fpm"]
