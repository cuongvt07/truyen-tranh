#!/usr/bin/env sh
set -eu

APP_ROOT="${APP_ROOT:-/var/www/html}"
ENV_FILE="${ENV_FILE:-$APP_ROOT/.env}"
IMPORT_FILE="${1:-}"

if [ -z "$IMPORT_FILE" ]; then
    echo "Usage: sh scripts/import-db.sh /path/to/export.sql.gz" >&2
    exit 1
fi

if [ ! -f "$IMPORT_FILE" ]; then
    echo "Import file not found: $IMPORT_FILE" >&2
    exit 1
fi

if [ -f "$ENV_FILE" ]; then
    set -a
    # shellcheck disable=SC1090
    . "$ENV_FILE"
    set +a
fi

DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

if [ "$DB_PASSWORD" = "null" ]; then
    DB_PASSWORD=""
fi

if [ "$DB_CONNECTION" != "mysql" ] && [ "$DB_CONNECTION" != "mariadb" ]; then
    echo "Only mysql/mariadb imports are supported. Current DB_CONNECTION=$DB_CONNECTION" >&2
    exit 1
fi

if [ -z "$DB_DATABASE" ]; then
    echo "DB_DATABASE is required." >&2
    exit 1
fi

MYSQL_BIN="$(command -v mysql || command -v mariadb || true)"

if [ -z "$MYSQL_BIN" ]; then
    echo "mysql/mariadb client is required in this container." >&2
    exit 1
fi

if echo "$IMPORT_FILE" | grep -q '\.gz$'; then
    gzip -dc "$IMPORT_FILE" | MYSQL_PWD="$DB_PASSWORD" "$MYSQL_BIN" \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --user="$DB_USERNAME" \
        --protocol=tcp \
        --default-character-set=utf8mb4 \
        "$DB_DATABASE"
else
    MYSQL_PWD="$DB_PASSWORD" "$MYSQL_BIN" \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --user="$DB_USERNAME" \
        --protocol=tcp \
        --default-character-set=utf8mb4 \
        "$DB_DATABASE" < "$IMPORT_FILE"
fi

echo "Imported: $IMPORT_FILE"
