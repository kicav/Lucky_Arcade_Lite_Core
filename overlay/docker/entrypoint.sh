#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is required. Generate one with: php artisan key:generate --show"
    exit 1
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if [ "${WAIT_FOR_DATABASE:-true}" = "true" ]; then
    echo "Waiting for database..."
    for attempt in $(seq 1 60); do
        if php artisan db:show --no-interaction >/dev/null 2>&1; then
            break
        fi
        if [ "$attempt" -eq 60 ]; then
            echo "Database did not become ready in time."
            exit 1
        fi
        sleep 2
    done
fi

php artisan optimize:clear --no-interaction >/dev/null

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
fi

exec "$@"
