FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress

FROM php:8.4-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libicu-dev \
        libpq-dev \
        libsqlite3-dev \
        libzip-dev \
        postgresql-client \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        pdo_sqlite \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY docker/php.ini /usr/local/etc/php/conf.d/99-lucky-arcade.ini
COPY docker/entrypoint.sh /usr/local/bin/arcade-entrypoint

RUN chmod +x /usr/local/bin/arcade-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
ENTRYPOINT ["arcade-entrypoint"]
CMD ["php-fpm"]
