#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/lucky-arcade-app"

[ -f "$APP/artisan" ] || {
  echo "Lucky Arcade app not found at: $APP"
  exit 1
}

FEATURE_TESTS=(
  LiteCoreSmokeTest.php
  WalletBetTest.php
  AdminLiteTest.php
  SecurityHeadersTest.php
)
UNIT_TESTS=(
  DiceEngineTest.php
  RouletteEngineTest.php
  CoinFlipEngineTest.php
  SlotsEngineTest.php
  ProvablyFairServiceTest.php
  TotpServiceTest.php
)

prune_dir() {
  local dir="$1"
  shift
  [ -d "$dir" ] || return 0

  local expression=()
  local name
  for name in "$@"; do
    expression+=( ! -name "$name" )
  done

  find "$dir" -maxdepth 1 -type f "${expression[@]}" -print -delete
}

echo "Removing legacy full-edition tests from overlay and generated app..."
prune_dir "$ROOT/overlay/tests/Feature" "${FEATURE_TESTS[@]}"
prune_dir "$ROOT/overlay/tests/Unit" "${UNIT_TESTS[@]}"
prune_dir "$APP/tests/Feature" "${FEATURE_TESTS[@]}"
prune_dir "$APP/tests/Unit" "${UNIT_TESTS[@]}"

mkdir -p "$ROOT/overlay/tests/Feature" "$APP/tests/Feature" "$ROOT/overlay/tests" "$APP/tests"
cp "$ROOT/overlay/tests/TestCase.php" "$APP/tests/TestCase.php"
cp "$ROOT/overlay/tests/Feature/LiteCoreSmokeTest.php" "$APP/tests/Feature/LiteCoreSmokeTest.php"

# Keep the corrected upgrade script in the repository so future Lite upgrades
# cannot copy stale tests back into lucky-arcade-app.
chmod +x "$ROOT/upgrade-lite-core.sh"

cd "$APP"
XDEBUG_MODE=off composer dump-autoload --no-interaction
XDEBUG_MODE=off php artisan optimize:clear
XDEBUG_MODE=off php artisan test --filter=LiteCoreSmokeTest
XDEBUG_MODE=off php artisan test
XDEBUG_MODE=off php artisan wallets:reconcile
XDEBUG_MODE=off php artisan arcade:verify-entries --limit=500
XDEBUG_MODE=off php artisan arcade:backup --keep=10
XDEBUG_MODE=off php artisan arcade:doctor

echo "Lucky Arcade Lite Core v1.0.2 hotfix applied successfully."
