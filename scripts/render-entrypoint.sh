#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/html"
if [ -d "${APP_DIR}" ]; then
  cd "${APP_DIR}"
fi

mkdir -p storage/framework/{cache,sessions,views}
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

php artisan optimize:clear || true

# Render expects the web process to bind to $PORT.
if [ -n "${PORT:-}" ]; then
  sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -ri "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
  sed -ri "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/default-ssl.conf 2>/dev/null || true
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  if command -v timeout >/dev/null 2>&1; then
    timeout "${MIGRATION_TIMEOUT_SECONDS:-60}" php artisan migrate --force || true
  else
    php artisan migrate --force || true
  fi
fi

exec apache2-foreground
