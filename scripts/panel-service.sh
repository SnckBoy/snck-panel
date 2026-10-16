#!/usr/bin/env bash
set -Eeuo pipefail
PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
export PATH

APP_DIR="${SNCK_PANEL_DIR:-/opt/snck-panel}"
SERVICE="snck-panel.service"
PM2_HOME="${SNCK_PM2_HOME:-/var/lib/snck/pm2}"
SERVICE_USER="${SNCK_SERVICE_USER:-snck}"
HELPER="${SNCK_SERVICE_HELPER:-/usr/local/bin/snck-panel-service}"
CRON_FILE="${SNCK_CRON_FILE:-/etc/cron.d/snck-panel}"

has_systemd() { [[ "${SNCK_FORCE_NO_SYSTEMD:-0}" != 1 ]] && command -v systemctl >/dev/null 2>&1 && [[ -d /run/systemd/system ]] && systemctl show-environment >/dev/null 2>&1; }
run_pm2() {
  local pm2="$APP_DIR/node_modules/.bin/pm2"
  [[ -x "$pm2" ]] || { echo "PM2 is missing at $pm2" >&2; return 1; }
  install -d -o "$SERVICE_USER" -g "$SERVICE_USER" -m 750 "$PM2_HOME"
  if [[ "$(id -un)" == "$SERVICE_USER" ]]; then env PM2_HOME="$PM2_HOME" PATH="/usr/local/bin:/usr/bin:/bin" "$pm2" "$@"
  else runuser -u "$SERVICE_USER" -- env PM2_HOME="$PM2_HOME" PATH="/usr/local/bin:/usr/bin:/bin" "$pm2" "$@"; fi
}

case "${1:-status}" in
  start)
    if has_systemd; then
      systemctl enable "$SERVICE" >/dev/null
      systemctl restart "$SERVICE"
    else
      run_pm2 describe snck-panel >/dev/null 2>&1 && run_pm2 restart snck-panel --update-env >/dev/null || run_pm2 start "$APP_DIR/dist/server.cjs" --name snck-panel --cwd "$APP_DIR" --interpreter "$(command -v node)" --time >/dev/null
      run_pm2 save >/dev/null
      echo "SNCK PANEL started under PM2. Reboot startup: $CRON_FILE"
    fi
    ;;
  stop)
    if has_systemd; then systemctl stop "$SERVICE"; else run_pm2 delete snck-panel >/dev/null 2>&1 || true; run_pm2 save >/dev/null 2>&1 || true; fi
    ;;
  restart)
    if has_systemd; then systemctl restart "$SERVICE"; else
      run_pm2 describe snck-panel >/dev/null 2>&1 && run_pm2 restart snck-panel --update-env >/dev/null || run_pm2 start "$APP_DIR/dist/server.cjs" --name snck-panel --cwd "$APP_DIR" --interpreter "$(command -v node)" --time >/dev/null
      run_pm2 save >/dev/null
    fi
    ;;
  status)
    if has_systemd; then systemctl status "$SERVICE" --no-pager; else run_pm2 status snck-panel; fi
    ;;
  logs)
    if has_systemd; then journalctl -u "$SERVICE" -n "${2:-100}" --no-pager; else run_pm2 logs snck-panel --lines "${2:-100}" --nostream; fi
    ;;
  resurrect)
    if has_systemd; then systemctl start "$SERVICE"; else run_pm2 resurrect; fi
    ;;
  boot-install)
    if has_systemd; then systemctl enable "$SERVICE"; else
      command -v cron >/dev/null 2>&1 || { echo "cron is required for reboot persistence without systemd" >&2; exit 1; }
      printf '@reboot root %s resurrect\n' "$HELPER" > "$CRON_FILE"
      chmod 644 "$CRON_FILE"
      if command -v systemctl >/dev/null 2>&1; then systemctl enable --now cron.service >/dev/null 2>&1 || service cron start; else service cron start; fi
      pgrep -x cron >/dev/null || { echo "Cron could not be started; reboot persistence is unavailable" >&2; exit 1; }
    fi
    ;;
  boot-remove)
    if has_systemd; then systemctl disable "$SERVICE" >/dev/null 2>&1 || true; else rm -f "$CRON_FILE"; fi
    ;;
  *) echo "Usage: $0 {start|stop|restart|status|logs [lines]|resurrect|boot-install|boot-remove}" >&2; exit 2 ;;
esac
