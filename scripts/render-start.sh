#!/usr/bin/env bash
set -euo pipefail

mkdir -p storage/framework/{cache,sessions,views}
mkdir -p bootstrap/cache

php artisan optimize:clear >/dev/null 2>&1 || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
