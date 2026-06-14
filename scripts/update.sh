#!/usr/bin/env bash
# Update PRODUCTION mà KHỎI build lại image (code bind-mount live).
# Chạy trên server:  bash scripts/update.sh
set -euo pipefail
cd "$(dirname "$0")/.."

DC="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> git pull"
git pull --ff-only

# composer install chỉ tốn thời gian khi composer.lock đổi (idempotent, an toàn)
echo "==> composer install (no-dev)"
$DC exec -T app composer install --no-dev --no-interaction --optimize-autoloader || true

echo "==> migrate"
$DC exec -T app php artisan migrate --force

echo "==> clear cache (code live: không cache lại)"
$DC exec -T app php artisan optimize:clear

echo "==> DONE (không build lại image)."
