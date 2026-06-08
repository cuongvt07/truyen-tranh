#!/bin/sh
set -e

cd /var/www

# -------------------------------------------------------
# 1. Ensure .env exists
# -------------------------------------------------------
if [ ! -f .env ]; then
    if [ -f .env.docker ]; then
        echo "[entrypoint] .env not found — copying from .env.docker"
        cp .env.docker .env
    else
        echo "[entrypoint] ERROR: no .env file and no .env.docker fallback"
        exit 1
    fi
fi

APP_ENV_VAL=$(grep -E "^APP_ENV=" .env 2>/dev/null | cut -d= -f2 | tr -d '"' | tr -d "'")
APP_ENV_VAL=${APP_ENV_VAL:-production}
echo "[entrypoint] APP_ENV=${APP_ENV_VAL}"

# -------------------------------------------------------
# 2. Install vendor if missing (only happens on dev bind-mount)
# -------------------------------------------------------
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] vendor missing — running composer install..."
    if [ "$APP_ENV_VAL" = "local" ]; then
        composer install --optimize-autoloader --no-interaction
    else
        composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
    fi
fi

# -------------------------------------------------------
# 3. Generate APP_KEY if not set
# -------------------------------------------------------
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "[entrypoint] generating APP_KEY..."
    php artisan key:generate --force
fi

# -------------------------------------------------------
# 4. Storage permissions
# -------------------------------------------------------
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# -------------------------------------------------------
# 5. Seed public/images from bundled defaults (first boot)
#    The volume is empty on first run; fill with default avatars/images.
# -------------------------------------------------------
if [ -d /var/www/_default_images ] && [ ! -f /var/www/public/images/.initialized ]; then
    echo "[entrypoint] seeding public/images from bundled defaults..."
    cp -rn /var/www/_default_images/. /var/www/public/images/
    touch /var/www/public/images/.initialized
fi

# -------------------------------------------------------
# 6. Storage symlink
# -------------------------------------------------------
if [ ! -L public/storage ]; then
    php artisan storage:link 2>/dev/null || true
fi

# -------------------------------------------------------
# 7. Database migrations (auto on every start)
# -------------------------------------------------------
echo "[entrypoint] running migrations..."
php artisan migrate --force 2>/dev/null || {
    echo "[entrypoint] WARN: migrate failed (DB might not be ready yet — will retry)"
    sleep 5
    php artisan migrate --force
}

# -------------------------------------------------------
# 8. Artisan cache strategy — prod vs dev
# -------------------------------------------------------
if [ "$APP_ENV_VAL" = "local" ]; then
    echo "[entrypoint] local: clearing caches..."
    php artisan config:clear  2>/dev/null || true
    php artisan route:clear   2>/dev/null || true
    php artisan view:clear    2>/dev/null || true
    php artisan event:clear   2>/dev/null || true
    php artisan package:discover --ansi 2>/dev/null || true
else
    echo "[entrypoint] production: building optimised caches..."
    php artisan config:cache  2>/dev/null || true
    php artisan route:cache   2>/dev/null || true
    php artisan view:cache    2>/dev/null || true
    php artisan event:cache   2>/dev/null || true
    php artisan package:discover --ansi 2>/dev/null || true
fi

echo "[entrypoint] ready — exec: $*"
exec "$@"
