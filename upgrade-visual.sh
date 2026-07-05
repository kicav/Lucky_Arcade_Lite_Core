#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/lucky-arcade-app"
[ -f "$APP/artisan" ] || { echo "Existing Lucky Arcade Lite Core app not found at $APP"; exit 1; }
STAMP="$(date +%Y%m%d-%H%M%S)"
DB="$APP/database/database.sqlite"
[ -f "$DB" ] && cp "$DB" "$DB.backup-before-visual-$STAMP"

echo "Applying Visual Edition assets and views..."
mkdir -p "$APP/public" "$APP/resources" "$APP/tests"
cp -R "$ROOT/overlay/public/." "$APP/public/"
cp -R "$ROOT/overlay/resources/." "$APP/resources/"
cp -R "$ROOT/overlay/tests/." "$APP/tests/"
cp -R "$ROOT/overlay/app/." "$APP/app/"
cp -R "$ROOT/overlay/bootstrap/." "$APP/bootstrap/"
cp -R "$ROOT/overlay/routes/." "$APP/routes/"
cp -R "$ROOT/docs" "$APP/docs"
rm -f "$APP/tests/Feature/ExampleTest.php" "$APP/tests/Unit/ExampleTest.php"

cd "$APP"
if grep -q '^APP_NAME=' .env; then
  sed -i 's/^APP_NAME=.*/APP_NAME="Lucky Arcade Visual"/' .env
fi
XDEBUG_MODE=off composer dump-autoload --no-interaction
XDEBUG_MODE=off php artisan optimize:clear
XDEBUG_MODE=off php artisan test --filter=VisualExperienceTest
XDEBUG_MODE=off php artisan test
XDEBUG_MODE=off php artisan wallets:reconcile
XDEBUG_MODE=off php artisan arcade:verify-entries --limit=500
XDEBUG_MODE=off php artisan arcade:backup --keep=10
XDEBUG_MODE=off php artisan arcade:doctor

echo "Lucky Arcade Visual upgrade completed. Database backup: $DB.backup-before-visual-$STAMP"
