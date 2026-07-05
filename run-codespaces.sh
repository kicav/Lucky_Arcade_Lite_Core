#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/lucky-arcade-app"
[ -f "$APP/artisan" ] || bash "$ROOT/setup-linux.sh"
cd "$APP"
if command -v ss >/dev/null && ss -ltn | grep -q ':8000 '; then
  echo "Port 8000 is already in use. Stop the old server first."; exit 1
fi
cleanup(){ [ -n "${SCHEDULER_PID:-}" ] && kill "$SCHEDULER_PID" 2>/dev/null || true; }
trap cleanup EXIT INT TERM
mkdir -p storage/logs
XDEBUG_MODE=off php artisan schedule:work > storage/logs/scheduler.log 2>&1 &
SCHEDULER_PID=$!
echo "Lucky Arcade Lite: http://0.0.0.0:8000"
XDEBUG_MODE=off php artisan serve --host=0.0.0.0 --port=8000
