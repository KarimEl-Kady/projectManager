# syntax=docker/dockerfile:1.7

FROM composer:2 AS composer-dependencies

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM node:24-alpine AS frontend-assets

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci --ignore-scripts

COPY resources ./resources
COPY vite.config.js ./

RUN npm run build

FROM php:8.3-fpm-alpine3.23 AS application

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apk add --no-cache \
        bash \
        curl \
        icu-libs \
        libxml2 \
        libzip \
        mysql-client \
        tzdata \
    && apk add --no-cache --virtual .build-dependencies \
        $PHPIZE_DEPS \
        curl-dev \
        icu-dev \
        libxml2-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        dom \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        xml \
        zip \
    && apk del .build-dependencies \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --chown=www-data:www-data . .
COPY --from=composer-dependencies --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend-assets --chown=www-data:www-data /app/public/build ./public/build
COPY docker/php/laravel.ini "$PHP_INI_DIR/conf.d/99-laravel.ini"

RUN mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
    && touch .env \
    && chown www-data:www-data .env \
    && chown -R www-data:www-data bootstrap/cache storage \
    && composer dump-autoload --no-interaction --optimize

EXPOSE 9000

CMD ["php-fpm", "-F"]

FROM nginx:1.28-alpine AS web

WORKDIR /var/www/html

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY public ./public
COPY --from=frontend-assets /app/public/build ./public/build

EXPOSE 80

HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=5 \
    CMD wget --quiet --tries=1 --spider http://127.0.0.1/up || exit 1
