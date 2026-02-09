#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

mkdir -p storage/framework/{cache,sessions,views}
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

php artisan optimize:clear || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
fi

exec apache2-foreground
