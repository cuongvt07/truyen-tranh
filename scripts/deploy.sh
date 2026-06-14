#!/usr/bin/env bash
# Deploy PRODUCTION lần đầu (build image 1 lần). Sau này dùng scripts/update.sh.
# Chạy trên server:  bash scripts/deploy.sh
set -euo pipefail
cd "$(dirname "$0")/.."

DC="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

if [ ! -f .env ]; then
  echo "!! Thiếu file .env (prod). Tạo ./.env với APP_KEY, APP_DEBUG=false, DB_*, REDIS_HOST=redis, HTTP_PORT=80 ..."
  exit 1
fi

echo "==> build (lần đầu, hoặc khi đổi Dockerfile/PHP ext/composer/npm dependency)"
$DC build

echo "==> up -d"
$DC up -d

echo "==> DONE. Update lần sau: bash scripts/update.sh"
