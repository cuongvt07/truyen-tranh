# syntax=docker/dockerfile:1.7

FROM composer:2.8 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/cache/composer \
    composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --optimize-autoloader

COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative


FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN --mount=type=cache,target=/root/.npm \
    if [ -f package.json ]; then \
        if [ -f package-lock.json ]; then npm ci; else npm install; fi; \
    fi

COPY . .
RUN if [ -f package.json ]; then npm run build; fi \
    && mkdir -p public/build


FROM php:8.3-fpm-alpine AS app

WORKDIR /var/www/html

ENV OPCACHE_VALIDATE_TIMESTAMPS=0 \
    OPCACHE_REVALIDATE_FREQ=0

RUN apk add --no-cache \
        bash \
        curl \
        freetype \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libzip \
        mysql-client \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

COPY --from=vendor --chown=www-data:www-data /app /var/www/html
COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

RUN mkdir -p \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/entrypoint.sh scripts/backup-comics-db.sh scripts/export-db.sh scripts/import-db.sh

USER www-data

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]


FROM nginx:1.27-alpine AS nginx

WORKDIR /var/www/html

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
