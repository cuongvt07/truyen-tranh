#!/usr/bin/env sh
set -eu

APP_ROOT="${APP_ROOT:-/var/www/html}"
ENV_FILE="${ENV_FILE:-$APP_ROOT/.env}"
BACKUP_DIR="${BACKUP_DIR:-$APP_ROOT/storage/app/backups}"
BACKUP_SCOPE="${BACKUP_SCOPE:-comics}"
DUMP_MODE="${DUMP_MODE:-data}"

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
    echo "Only mysql/mariadb exports are supported. Current DB_CONNECTION=$DB_CONNECTION" >&2
    exit 1
fi

if [ -z "$DB_DATABASE" ]; then
    echo "DB_DATABASE is required." >&2
    exit 1
fi

MYSQL_BIN="$(command -v mysql || command -v mariadb || true)"
DUMP_BIN="$(command -v mysqldump || command -v mariadb-dump || true)"

if [ -z "$MYSQL_BIN" ] || [ -z "$DUMP_BIN" ]; then
    echo "mysql/mariadb client and dump tools are required in this container." >&2
    exit 1
fi

mkdir -p "$BACKUP_DIR"

if [ "$BACKUP_SCOPE" = "all" ]; then
    TABLE_QUERY="
SELECT table_name
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_type = 'BASE TABLE'
ORDER BY table_name;
"
else
    TABLE_QUERY="
SELECT table_name
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_type = 'BASE TABLE'
  AND (
    table_name IN (
      'comics',
      'comic',
      'stories',
      'story',
      'mangas',
      'manga',
      'truyen',
      'truyens',
      'chapters',
      'chapter',
      'chapter_images',
      'pages',
      'images',
      'categories',
      'genres',
      'tags',
      'authors',
      'artists',
      'translators',
      'publishers',
      'statuses',
      'comic_statuses',
      'story_statuses',
      'manga_statuses'
    )
    OR table_name LIKE '%comic%'
    OR table_name LIKE '%story%'
    OR table_name LIKE '%manga%'
    OR table_name LIKE '%truyen%'
    OR table_name LIKE '%chapter%'
    OR table_name LIKE '%category%'
    OR table_name LIKE '%genre%'
    OR table_name LIKE '%tag%'
    OR table_name LIKE '%author%'
    OR table_name LIKE '%artist%'
    OR table_name LIKE '%translator%'
    OR table_name LIKE '%publisher%'
    OR table_name LIKE '%status%'
    OR table_name LIKE '%bookmark%'
    OR table_name LIKE '%favorite%'
    OR table_name LIKE '%follow%'
    OR table_name LIKE '%rating%'
    OR table_name LIKE '%comment%'
    OR table_name LIKE '%reading%'
    OR table_name LIKE '%history%'
    OR table_name LIKE '%view%'
  )
ORDER BY table_name;
"
fi

TABLES="$(
    MYSQL_PWD="$DB_PASSWORD" "$MYSQL_BIN" \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --user="$DB_USERNAME" \
        --protocol=tcp \
        --batch \
        --skip-column-names \
        "$DB_DATABASE" \
        --execute "$TABLE_QUERY"
)"

if [ -z "$TABLES" ]; then
    echo "No tables found for BACKUP_SCOPE=$BACKUP_SCOPE in database $DB_DATABASE." >&2
    exit 1
fi

TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
OUTPUT="$BACKUP_DIR/${BACKUP_SCOPE}-${DUMP_MODE}-db-$DB_DATABASE-$TIMESTAMP.sql.gz"
TABLE_FILE="$BACKUP_DIR/${BACKUP_SCOPE}-${DUMP_MODE}-db-$DB_DATABASE-$TIMESTAMP.tables.txt"
MODE_ARGS=""

if [ "$DUMP_MODE" = "data" ]; then
    MODE_ARGS="--no-create-info --skip-triggers --replace"
elif [ "$DUMP_MODE" = "full" ]; then
    MODE_ARGS="--add-drop-table --routines --triggers"
else
    echo "DUMP_MODE must be data or full." >&2
    exit 1
fi

printf "%s\n" "$TABLES" > "$TABLE_FILE"

# shellcheck disable=SC2086
MYSQL_PWD="$DB_PASSWORD" "$DUMP_BIN" \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    --protocol=tcp \
    --single-transaction \
    --quick \
    --skip-lock-tables \
    --default-character-set=utf8mb4 \
    $MODE_ARGS \
    "$DB_DATABASE" \
    $TABLES \
    | gzip -9 > "$OUTPUT"

echo "$OUTPUT"
