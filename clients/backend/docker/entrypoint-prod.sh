#!/bin/sh
# Production entrypoint for the Laravel backend container.
# Runs idempotent boot tasks (migrate) on the primary php-fpm container ONLY,
# then exec's the original CMD. Other containers built from the same image
# (e.g. backend-cron, rag-worker) override CMD and skip the boot tasks.

set -e

# Only the primary php-fpm container runs migrations + cache warmup. This avoids
# races with sibling containers (cron, worker) starting from the same image.
if [ "$1" = "php-fpm" ]; then
  echo "[entrypoint] Running database migrations (force, idempotent)..."
  php artisan migrate --force --no-interaction || {
    echo "[entrypoint] Migration failed — aborting startup"
    exit 1
  }

  echo "[entrypoint] Warming caches..."
  php artisan config:cache --no-interaction || true
  php artisan route:cache --no-interaction || true
  php artisan view:cache --no-interaction || true
fi

exec "$@"
