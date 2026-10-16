#!/usr/bin/env bash
set -Eeuo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
APP="$TMP/app"
mkdir -p "$APP/node_modules/.bin" "$APP/dist"
touch "$APP/dist/server.cjs"
cat > "$APP/node_modules/.bin/pm2" <<'MOCK'
#!/usr/bin/env bash
printf '%s\n' "$*" >> "$PM2_TEST_LOG"
if [[ "${1:-}" == describe && "${PM2_DESCRIBE_RESULT:-0}" != 1 ]]; then exit 1; fi
MOCK
chmod +x "$APP/node_modules/.bin/pm2"
export SNCK_PANEL_DIR="$APP"
export SNCK_PM2_HOME="$TMP/pm2-home"
export SNCK_SERVICE_USER="$(id -un)"
export SNCK_SERVICE_HELPER="$TMP/snck-panel-service"
export SNCK_CRON_FILE="$TMP/cron.d/snck-panel"
export SNCK_FORCE_NO_SYSTEMD=1
export PM2_TEST_LOG="$TMP/pm2.log"

export PM2_DESCRIBE_RESULT=0
if bash "$ROOT/scripts/panel-service.sh" start >/dev/null; then :; fi
export PM2_DESCRIBE_RESULT=1
for action in start restart status logs resurrect stop; do
  [[ "$action" == start ]] && continue
  if [[ "$action" == logs ]]; then bash "$ROOT/scripts/panel-service.sh" "$action" 7 >/dev/null
  else bash "$ROOT/scripts/panel-service.sh" "$action" >/dev/null
  fi
done
bash "$ROOT/scripts/panel-service.sh" boot-remove
for expected in "start $APP/dist/server.cjs" 'restart snck-panel --update-env' 'status snck-panel' 'logs snck-panel --lines 7 --nostream' 'resurrect' 'delete snck-panel' 'save'; do
  grep -Fq "$expected" "$PM2_TEST_LOG" || { printf 'Expected PM2 call missing: %s\n' "$expected" >&2; cat "$PM2_TEST_LOG" >&2; exit 1; }
done
[[ ! -e "$SNCK_CRON_FILE" ]]
printf 'Non-systemd PM2 service fallback tests passed.\n'
