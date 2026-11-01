#!/usr/bin/env bash
set -Eeuo pipefail

# Root entry point for the Laravel/PHP panel. The branch defaults to main;
# use SNCK_BRANCH=... to install a different branch explicitly.
BRANCH="${SNCK_BRANCH:-main}"
REPO_URL="${SNCK_REPO_URL:-https://github.com/SnckBoy/snck-panel.git}"
FORWARD_ARGS=()

while [[ $# -gt 0 ]]; do
  case "$1" in
    --branch=*) BRANCH="${1#*=}"; shift ;;
    --repo=*) REPO_URL="${1#*=}"; shift ;;
    -h|--help)
      cat <<'EOF'
SNCK Panel — Laravel installer entry point

Usage:
  curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/main/install.sh | sudo bash

Options:
  --branch=BRANCH
  --repo=GIT_URL
  --dry-run
  --app-dir=/absolute/path
  --help

Prerequisites: PHP 8.3+, Composer, Node.js 20+, npm, and a configured PostgreSQL database.
EOF
      exit 0
      ;;
    *) FORWARD_ARGS+=("$1"); shift ;;
  esac
done

# Interactive installer menu. In non-interactive automation, default to install.
if [[ -r /dev/tty && "${SNCK_NONINTERACTIVE:-0}" != "1" ]]; then
  printf '\nSNCK Panel Installer\n'
  printf '  [1] Fresh install\n  [2] Update existing panel\n  [3] Repair / reinstall dependencies\n  [4] Diagnostics\n  [5] Uninstall\n'
  read -r -p 'Choose an option [1-5]: ' MENU_CHOICE </dev/tty
  case "${MENU_CHOICE:-1}" in
    1|2|3) ;;
    4)
      for unit in nginx php8.3-fpm postgresql snck-panel-laravel snck-panel-laravel-scheduler.timer snck-panel-discord-worker.timer; do
        systemctl status "$unit" --no-pager || true
      done
      exit 0
      ;;
    5)
      read -r -p 'Type UNINSTALL to remove SNCK Panel and its service configuration: ' CONFIRM </dev/tty
      [[ "$CONFIRM" == "UNINSTALL" ]] || { echo 'Uninstall cancelled.'; exit 0; }
      rm -f /etc/nginx/sites-enabled/snck-panel-laravel /etc/nginx/sites-available/snck-panel-laravel
      rm -f /etc/php/8.3/fpm/pool.d/snck-panel.conf
      rm -f /etc/systemd/system/snck-panel-laravel.service /etc/systemd/system/snck-panel-laravel-scheduler.service /etc/systemd/system/snck-panel-laravel-scheduler.timer /etc/systemd/system/snck-panel-discord-worker.service /etc/systemd/system/snck-panel-discord-worker.timer
      systemctl daemon-reload
      systemctl reload nginx 2>/dev/null || true
      rm -rf "${SNCK_PANEL_DIR:-/opt/snck-panel-laravel}"
      echo 'SNCK Panel application and service configuration removed. PostgreSQL data was kept.'
      exit 0
      ;;
    *) echo 'Invalid menu option.' >&2; exit 2 ;;
  esac
fi

INSTALLER_URL="${SNCK_INSTALLER_URL:-https://raw.githubusercontent.com/SnckBoy/snck-panel/$BRANCH/scripts/install-laravel-panel.sh}"

if [[ "$EUID" -ne 0 ]]; then
  command -v sudo >/dev/null 2>&1 || { echo 'ERROR: sudo is required. Run as root.' >&2; exit 1; }
fi
command -v curl >/dev/null 2>&1 || { echo 'ERROR: curl is required.' >&2; exit 1; }
TMP="$(mktemp /tmp/snck-panel-laravel-installer.XXXXXX)"
trap 'rm -f -- "$TMP"' EXIT
curl -fsSL --retry 3 --connect-timeout 10 --max-time 120 "$INSTALLER_URL" -o "$TMP"
chmod 700 "$TMP"

if [[ "$EUID" -eq 0 ]]; then
  SNCK_REPO_URL="$REPO_URL" bash "$TMP" --branch="$BRANCH" "${FORWARD_ARGS[@]}"
else
  PRESERVE_ENV="SNCK_DB_HOST,SNCK_DB_PORT,SNCK_DB_DATABASE,SNCK_DB_USERNAME,SNCK_DB_PASSWORD,SNCK_DB_SSLMODE,SNCK_APP_URL,SNCK_PANEL_DIR"
  sudo --preserve-env="$PRESERVE_ENV" env SNCK_REPO_URL="$REPO_URL" bash "$TMP" --branch="$BRANCH" "${FORWARD_ARGS[@]}"
fi
