#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VAR_DIR="$APP_DIR/var"
DATA_DIR="$VAR_DIR/mysql"
SOCKET="$VAR_DIR/mysql.sock"
PID_FILE="$VAR_DIR/mysql.pid"
LOG_FILE="$VAR_DIR/mysql.log"
PORT="${PORT:-5000}"
DB_PORT="${DB_PORT:-3310}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_NAME="no_more_silence"
DB_USER="nms_app"
DB_PASS=""

mkdir -p "$VAR_DIR" "$APP_DIR/storage/evidence"

if ! mariadb-admin --socket="$SOCKET" --user=root ping >/dev/null 2>&1; then
  if [ ! -d "$DATA_DIR/mysql" ]; then
    mariadb-install-db --datadir="$DATA_DIR" --auth-root-authentication-method=normal --skip-test-db --user="$(id -un)" >/dev/null
  fi
  mariadbd --user="$(id -un)" --datadir="$DATA_DIR" --socket="$SOCKET" --pid-file="$PID_FILE" \
    --port="$DB_PORT" --bind-address=127.0.0.1 --skip-name-resolve --log-error="$LOG_FILE" &
  DB_PID=$!
  for attempt in $(seq 1 40); do
    if mariadb-admin --socket="$SOCKET" --user=root ping >/dev/null 2>&1; then break; fi
    sleep 0.5
  done
  if ! mariadb-admin --socket="$SOCKET" --user=root ping >/dev/null 2>&1; then
    cat "$LOG_FILE" >&2 || true
    exit 1
  fi
else
  DB_PID=""
fi

mariadb --socket="$SOCKET" --user=root < "$APP_DIR/install.sql"
mariadb --socket="$SOCKET" --user=root <<'SQL'
CREATE USER IF NOT EXISTS 'nms_app'@'127.0.0.1' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON no_more_silence.* TO 'nms_app'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

export APP_ENV=development DB_HOST DB_PORT DB_NAME DB_USER DB_PASS
export APP_HASH_SALT="${APP_HASH_SALT:-${SESSION_SECRET:-local-development-only-change-before-production}}"
php "$APP_DIR/bin/generate-sitemap.php" >/dev/null
cd "$APP_DIR"
php -d upload_max_filesize=5M -d post_max_size=35M -d max_file_uploads=7 \
  -S "0.0.0.0:${PORT}" -t "$APP_DIR/public" "$APP_DIR/public/router.php" &
PHP_PID=$!
cleanup() {
  kill "$PHP_PID" 2>/dev/null || true
  if [ -n "${DB_PID:-}" ]; then kill "$DB_PID" 2>/dev/null || true; fi
}
trap cleanup EXIT INT TERM
wait "$PHP_PID"
