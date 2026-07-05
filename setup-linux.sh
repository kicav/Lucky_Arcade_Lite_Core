#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TARGET="$ROOT/lucky-arcade-app"
command -v php >/dev/null || { echo "PHP is required."; exit 1; }
command -v composer >/dev/null || { echo "Composer is required."; exit 1; }
[ "$(php -r 'echo PHP_VERSION_ID;')" -ge 80300 ] || { echo "PHP 8.3+ is required."; exit 1; }
php -m | grep -qi '^pdo_sqlite$' || { echo "pdo_sqlite is required for Codespaces."; exit 1; }
if [ -d "$TARGET" ] && [ ! -f "$TARGET/artisan" ]; then rm -rf "$TARGET"; fi
if [ -f "$TARGET/artisan" ]; then echo "Already installed: $TARGET"; exit 0; fi
XDEBUG_MODE=off composer create-project --no-interaction --prefer-dist laravel/laravel "$TARGET" "^13.0"
cp -R "$ROOT/overlay/." "$TARGET/"
cp "$ROOT/LICENSE" "$TARGET/LICENSE"
cp -R "$ROOT/docs" "$TARGET/docs"
cd "$TARGET"
cp .env.example .env
sed -i 's/^APP_NAME=.*/APP_NAME="Lucky Arcade Lite"/' .env
sed -i 's/^APP_ENV=.*/APP_ENV=local/' .env
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=true/' .env
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=database/' .env
sed -i 's/^CACHE_STORE=.*/CACHE_STORE=database/' .env
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=database/' .env
if [ -n "${CODESPACE_NAME:-}" ]; then
  URL="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
  sed -i "s#^APP_URL=.*#APP_URL=${URL}#" .env
fi
mkdir -p database && touch database/database.sqlite
XDEBUG_MODE=off php artisan key:generate --force
XDEBUG_MODE=off php artisan migrate --seed --force
XDEBUG_MODE=off php artisan test
XDEBUG_MODE=off php artisan wallets:reconcile
XDEBUG_MODE=off php artisan arcade:verify-entries --limit=200
XDEBUG_MODE=off php artisan arcade:backup --keep=10
XDEBUG_MODE=off php artisan arcade:doctor
echo "Lite Core installed. Run: bash run-codespaces.sh"
