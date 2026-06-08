#!/usr/bin/env sh
set -eu

if [ -f artisan ]; then
    if [ "${APP_ENV:-production}" = "local" ] || [ "${APP_ENV:-production}" = "dev" ] || [ "${APP_ENV:-production}" = "development" ]; then
        php artisan optimize:clear --no-interaction || true
    else
        if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
            php artisan migrate --force --no-interaction
        fi

        if [ "${RUN_SEEDERS:-false}" = "true" ]; then
            php artisan db:seed --force --no-interaction
        fi

        if [ "${RUN_CACHE_WARMUP:-true}" = "true" ]; then
            php artisan config:cache --no-interaction
            php artisan route:cache --no-interaction || true
            php artisan view:cache --no-interaction
            php artisan event:cache --no-interaction || true
        fi
    fi
fi

exec "$@"
