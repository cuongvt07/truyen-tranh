#!/usr/bin/env bash
# Update PRODUCTION mà KHỎI build lại image (code bind-mount live).
# Chạy trên server:  bash scripts/update.sh
set -euo pipefail
cd "$(dirname "$0")/.."

DC="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> git pull"
git pull --ff-only

# Image runtime KHÔNG có composer -> dùng composer one-off container, mount source
# (bind-mount) + vendor (named volume). install (idempotent khi lock không đổi) RỒI
# dump-autoload --optimize để class mới từ git pull chắc chắn vào classmap.
# KHÔNG nuốt lỗi (bỏ "|| true") để deploy fail-loud nếu autoload hỏng.
PROJECT="${COMPOSE_PROJECT_NAME:-$(basename "$PWD")}"
echo "==> composer install + dump-autoload (one-off composer:2)"
docker run --rm \
  -v "$PWD":/app \
  -v "${PROJECT}_app-vendor":/app/vendor \
  -w /app composer:2 \
  sh -c "composer install --no-dev --no-interaction --optimize-autoloader && composer dump-autoload --no-dev --optimize"

echo "==> migrate"
$DC exec -T app php artisan migrate --force

echo "==> clear cache (code live: không cache lại)"
$DC exec -T app php artisan optimize:clear

echo "==> DONE (không build lại image)."
