#!/usr/bin/env bash
set -Eeuo pipefail

APP_NAME="SNCK PANEL"
APP_DIR="${SNCK_PANEL_DIR:-/opt/snck-panel}"
REPO_URL="${SNCK_REPO_URL:-https://github.com/SnckBoy/snck-panel.git}"
BRANCH="${SNCK_BRANCH:-main}"
SERVICE="snck-panel.service"
PORT="${SNCK_PORT:-6767}"
INSTALLER_URL="${SNCK_INSTALLER_URL:-https://raw.githubusercontent.com/SnckBoy/snck-panel/main/scripts/install-panel.sh}"
LOG_FILE="/var/log/snck-panel-installer.log"
BACKUP_DIR="${SNCK_BACKUP_DIR:-/var/backups/snck-panel}"

if [[ $EUID -ne 0 ]]; then
  exec sudo -E bash "$0" "$@"
fi

clear_screen(){ command -v clear >/dev/null 2>&1 && clear || true; }
header(){
  clear_screen
  printf '\n\033[1;35m╭──────────────────────────────────────────────╮\033[0m\n'
  printf '\033[1;35m│\033[0m        \033[1;97mSNCK PANEL CONTROL CENTER\033[0m         \033[1;35m│\033[0m\n'
  printf '\033[1;35m│\033[0m          \033[2mInstall • Update • Repair\033[0m          \033[1;35m│\033[0m\n'
  printf '\033[1;35m╰──────────────────────────────────────────────╯\033[0m\n\n'
}

pause(){ printf '\nPress Enter to continue... '; read -r _ || true; }

service_exists(){ systemctl list-unit-files "$SERVICE" --no-legend 2>/dev/null | grep -q "^${SERVICE}[[:space:]]"; }
installed(){ [[ -f "$APP_DIR/package.json" ]] && service_exists; }

run_installer(){
  local mode="$1"
  printf '\n\033[1;36m[%s]\033[0m Starting %s...\n' "$mode" "$APP_NAME"
  curl -fsSL --retry 3 --connect-timeout 10 --max-time 120 "$INSTALLER_URL" -o /tmp/snck-panel-installer.sh
  chmod 700 /tmp/snck-panel-installer.sh
  SNCK_INSTALL_MODE="$mode" bash /tmp/snck-panel-installer.sh
  rm -f /tmp/snck-panel-installer.sh
}

install_panel(){
  run_installer "install"
  pause
}

update_panel(){
  if ! installed; then
    printf '\n\033[1;33mSNCK PANEL is not installed yet.\033[0m\n'
    install_panel
    return
  fi
  run_installer "update"
  pause
}

repair_panel(){
  if ! installed; then
    printf '\n\033[1;33mSNCK PANEL is not installed yet; repair will perform a fresh install.\033[0m\n'
  fi
  run_installer "repair"
  pause
}

status_panel(){
  printf '\n\033[1;36mService status\033[0m\n'
  if service_exists; then
    systemctl status "$SERVICE" --no-pager || true
  else
    printf 'Service: not installed\n'
  fi
  printf '\n\033[1;36mHealth check\033[0m\n'
  if curl -fsS --max-time 5 "http://127.0.0.1:${PORT}/health" >/dev/null 2>&1; then
    printf '\033[1;32m● Healthy\033[0m  http://127.0.0.1:%s/health\n' "$PORT"
  else
    printf '\033[1;31m● Unreachable\033[0m  http://127.0.0.1:%s/health\n' "$PORT"
  fi
  pause
}

restart_panel(){
  if service_exists; then
    systemctl restart "$SERVICE"
    systemctl is-active --quiet "$SERVICE" && printf '\033[1;32mSNCK PANEL restarted successfully.\033[0m\n' || printf '\033[1;31mSNCK PANEL failed to restart.\033[0m\n'
  else
    printf '\033[1;33mSNCK PANEL service is not installed.\033[0m\n'
  fi
  pause
}

logs_panel(){
  printf '\n\033[1;36mRecent service logs\033[0m\n\n'
  if service_exists; then journalctl -u "$SERVICE" -n 100 --no-pager || true; else printf 'Service is not installed.\n'; fi
  printf '\n\033[1;36mInstaller log\033[0m: %s\n' "$LOG_FILE"
  [[ -f "$LOG_FILE" ]] && tail -n 40 "$LOG_FILE" || printf 'No installer log yet.\n'
  pause
}

backup_panel(){
  mkdir -p "$BACKUP_DIR"
  chmod 700 "$BACKUP_DIR"
  local stamp dest
  stamp="$(date +%Y%m%d-%H%M%S)"
  dest="$BACKUP_DIR/manual-$stamp"
  mkdir -p "$dest"
  if [[ -d "$APP_DIR/.data" ]]; then cp -a "$APP_DIR/.data" "$dest/.data"; fi
  if [[ -f "$APP_DIR/.env" ]]; then cp -a "$APP_DIR/.env" "$dest/.env"; fi
  if [[ -f "$APP_DIR/package.json" ]]; then cp -a "$APP_DIR/package.json" "$dest/package.json"; fi
  chmod 700 "$dest"
  printf '\033[1;32mBackup created:\033[0m %s\n' "$dest"
  pause
}

system_info(){
  printf '\n\033[1;36mSystem information\033[0m\n\n'
  printf 'OS:       '; . /etc/os-release 2>/dev/null; printf '%s %s\n' "${PRETTY_NAME:-unknown}" "${VERSION_ID:-}"
  printf 'Kernel:   %s\n' "$(uname -r)"
  printf 'Arch:     %s\n' "$(dpkg --print-architecture 2>/dev/null || uname -m)"
  printf 'Node:     %s\n' "$(node --version 2>/dev/null || echo not-installed)"
  printf 'npm:      %s\n' "$(npm --version 2>/dev/null || echo not-installed)"
  printf 'Docker:   %s\n' "$(docker --version 2>/dev/null || echo not-installed)"
  printf 'Postgres: %s\n' "$(psql --version 2>/dev/null || echo not-installed)"
  printf 'Panel:    %s\n' "$(installed && echo installed || echo not-installed)"
  pause
}

uninstall_panel(){
  printf '\n\033[1;31mWARNING: This removes the SNCK PANEL service and application files.\033[0m\n'
  printf 'Backups in %s are preserved.\n\n' "$BACKUP_DIR"
  read -r -p 'Type UNINSTALL to continue: ' confirm
  [[ "$confirm" == "UNINSTALL" ]] || { printf 'Cancelled.\n'; pause; return; }

  if service_exists; then
    systemctl disable --now "$SERVICE" 2>/dev/null || true
    rm -f "/etc/systemd/system/$SERVICE"
    systemctl daemon-reload
    systemctl reset-failed "$SERVICE" 2>/dev/null || true
  fi
  rm -rf "$APP_DIR"
  rm -f /usr/local/bin/snck-panel
  printf '\033[1;32mSNCK PANEL application/service removed.\033[0m\n'
  printf 'PostgreSQL database and backups were NOT removed.\n'
  pause
}

while true; do
  header
  printf '  \033[1;97m1)\033[0m Install SNCK PANEL\n'
  printf '  \033[1;97m2)\033[0m Update SNCK PANEL\n'
  printf '  \033[1;97m3)\033[0m Repair / Reinstall files\n'
  printf '  \033[1;97m4)\033[0m Status + health check\n'
  printf '  \033[1;97m5)\033[0m Restart panel\n'
  printf '  \033[1;97m6)\033[0m View logs\n'
  printf '  \033[1;97m7)\033[0m Create backup\n'
  printf '  \033[1;97m8)\033[0m System information\n'
  printf '  \033[1;97m9)\033[0m Uninstall panel\n'
  printf '  \033[1;97m0)\033[0m Exit\n\n'
  read -r -p '  Select an option [0-9]: ' choice || exit 0
  case "$choice" in
    1) install_panel ;;
    2) update_panel ;;
    3) repair_panel ;;
    4) status_panel ;;
    5) restart_panel ;;
    6) logs_panel ;;
    7) backup_panel ;;
    8) system_info ;;
    9) uninstall_panel ;;
    0) exit 0 ;;
    *) printf '\n\033[1;33mInvalid option. Choose 0-9.\033[0m\n'; sleep 1 ;;
  esac
done
