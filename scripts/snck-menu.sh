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

# Pick a writable temporary directory without changing permissions on shared dirs.
choose_tmp_dir() {
  local dir probe
  for dir in "${TMPDIR:-}" /root/.cache/snck-panel/tmp /var/tmp /tmp; do
    [[ -n "$dir" ]] || continue
    if [[ "$dir" == "/root/.cache/snck-panel/tmp" ]]; then
      mkdir -p "$dir" 2>/dev/null || continue
      chmod 700 "$dir" 2>/dev/null || continue
    fi
    [[ -d "$dir" && -w "$dir" ]] || continue
    probe="$(mktemp "$dir/.snck-probe.XXXXXX" 2>/dev/null)" || continue
    rm -f -- "$probe"
    printf '%s' "$dir"
    return 0
  done
  return 1
}
BACKUP_DIR="${SNCK_BACKUP_DIR:-/var/backups/snck-panel}"
SERVICE_HELPER="/usr/local/bin/snck-panel-service"

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

has_systemd(){ command -v systemctl >/dev/null 2>&1 && [[ -d /run/systemd/system ]] && systemctl show-environment >/dev/null 2>&1; }
service_exists(){ if has_systemd; then systemctl list-unit-files "$SERVICE" --no-legend 2>/dev/null | grep -q "^${SERVICE}[[:space:]]"; else [[ -x "$SERVICE_HELPER" && -f "$APP_DIR/dist/server.cjs" ]]; fi; }
installed(){ [[ -f "$APP_DIR/package.json" ]] && service_exists; }

run_installer(){
  local mode="$1"
  printf '\n\033[1;36m[%s]\033[0m Starting %s...\n' "$mode" "$APP_NAME"
  local tmp_base tmp_file rc
  tmp_base="$(choose_tmp_dir)" || { printf 'ERROR: No writable temporary directory found.\\n' >&2; return 1; }
  tmp_file="$(mktemp "$tmp_base/snck-panel-installer.XXXXXX")"
  if ! curl -fsSL --retry 3 --connect-timeout 10 --max-time 120 "$INSTALLER_URL" -o "$tmp_file"; then
    rm -f -- "$tmp_file"
    printf 'ERROR: Could not download installer.\\n' >&2
    return 1
  fi
  chmod 700 "$tmp_file"
  if SNCK_INSTALL_MODE="$mode" bash "$tmp_file"; then
    rc=0
  else
    rc=$?
  fi
  rm -f -- "$tmp_file"
  return "$rc"
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
  if service_exists && has_systemd; then
    systemctl status "$SERVICE" --no-pager || true
  elif service_exists; then
    "$SERVICE_HELPER" status || true
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
  if service_exists && has_systemd; then
    systemctl restart "$SERVICE"
    systemctl is-active --quiet "$SERVICE" && printf '\033[1;32mSNCK PANEL restarted successfully.\033[0m\n' || printf '\033[1;31mSNCK PANEL failed to restart.\033[0m\n'
  elif service_exists; then
    "$SERVICE_HELPER" restart && printf '\033[1;32mSNCK PANEL restarted successfully.\033[0m\n' || printf '\033[1;31mSNCK PANEL failed to restart.\033[0m\n'
  else
    printf '\033[1;33mSNCK PANEL service is not installed.\033[0m\n'
  fi
  pause
}

logs_panel(){
  printf '\n\033[1;36mRecent service logs\033[0m\n\n'
  if service_exists && has_systemd; then journalctl -u "$SERVICE" -n 100 --no-pager || true
  elif service_exists; then "$SERVICE_HELPER" logs 100 || true
  else printf 'Service is not installed.\n'; fi
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
  printf 'Storage:  JSON files in %s/.data\n' "$APP_DIR"
  printf 'Panel:    %s\n' "$(installed && echo installed || echo not-installed)"
  pause
}

uninstall_panel(){
  if [[ ! -r /dev/tty ]]; then
    printf 'ERROR: Uninstall requires an interactive terminal.\n' >&2
    return 2
  fi

  printf '\n\033[1;31mSNCK UNINSTALL MENU\033[0m\n'
  printf '  1) Node Only — remove local node daemon; preserve panel and Minecraft server files\n'
  printf '  2) Panel Only — remove panel app/data/backups; preserve node and Minecraft server files\n'
  printf '  3) All — remove panel, local node, SNCK-managed Minecraft data and backups\n'
  printf '  0) Cancel\n\n'
  local choice confirm
  read -r -p 'Choose [0-3]: ' choice </dev/tty
  case "$choice" in
    0) printf 'Cancelled. Nothing was removed.\n'; return 0 ;;
    1)
      printf '\nNode Only will stop/remove the local node daemon and its program/config files.\n'
      printf 'It will NOT remove Minecraft server files, Docker containers/images/volumes, or the panel.\n'
      read -r -p 'Type REMOVE-NODE to continue: ' confirm </dev/tty
      [[ "$confirm" == "REMOVE-NODE" ]] || { printf 'Cancelled.\n'; return 0; }
      systemctl disable --now shironex-node.service 2>/dev/null || true
      rm -f /etc/systemd/system/shironex-node.service
      rm -rf -- /opt/shironex-node /etc/shironex-node
      rm -f /usr/local/bin/shironex-node /usr/local/bin/shironex-node-daemon
      systemctl daemon-reload 2>/dev/null || true
      systemctl reset-failed shironex-node.service 2>/dev/null || true
      printf '\nNode daemon removed. Minecraft files and Docker resources were preserved.\n'
      ;;
    2)
      printf '\nPanel Only will remove panel service, app files, panel data/config, and panel backups.\n'
      printf 'The local node daemon, Minecraft server files, and Docker resources will be preserved.\n'
      read -r -p 'Type REMOVE-PANEL to continue: ' confirm </dev/tty
      [[ "$confirm" == "REMOVE-PANEL" ]] || { printf 'Cancelled.\n'; return 0; }
      if [[ -x "$SERVICE_HELPER" ]]; then "$SERVICE_HELPER" stop >/dev/null 2>&1 || true; "$SERVICE_HELPER" boot-remove >/dev/null 2>&1 || true; fi
      systemctl disable --now "$SERVICE" 2>/dev/null || true
      rm -f "/etc/systemd/system/$SERVICE"
      rm -rf -- "$APP_DIR" "$BACKUP_DIR" /etc/snck-panel /var/lib/snck-panel /var/log/snck-panel /var/lib/snck
      rm -f -- /usr/local/bin/snck-panel "$SERVICE_HELPER" "$LOG_FILE" /etc/cron.d/snck-panel
      if id -u snck >/dev/null 2>&1; then userdel snck >/dev/null 2>&1 || true; fi
      systemctl daemon-reload 2>/dev/null || true
      systemctl reset-failed "$SERVICE" 2>/dev/null || true
      printf '\nPanel removed. Node daemon, Minecraft files, and Docker resources were preserved.\n'
      ;;
    3)
      printf '\n\033[1;31mDANGER: ALL SNCK COMPONENTS AND SERVER DATA\033[0m\n'
      printf 'This removes the panel, local node daemon, Minecraft server files, SNCK-managed Docker containers/images/volumes, and panel backups.\n'
      printf 'Docker and Node.js packages themselves are left installed. A node on another VPS must be removed separately.\n'
      read -r -p 'Type DELETE-SNCK-ALL to permanently continue: ' confirm </dev/tty
      [[ "$confirm" == "DELETE-SNCK-ALL" ]] || { printf 'Cancelled.\n'; return 0; }

      if [[ -x "$SERVICE_HELPER" ]]; then "$SERVICE_HELPER" stop >/dev/null 2>&1 || true; "$SERVICE_HELPER" boot-remove >/dev/null 2>&1 || true; fi
      systemctl disable --now "$SERVICE" 2>/dev/null || true
      systemctl disable --now shironex-node.service 2>/dev/null || true
      rm -f "/etc/systemd/system/$SERVICE" /etc/systemd/system/shironex-node.service
      if command -v docker >/dev/null 2>&1; then
        managed_ids="$(docker ps -aq --filter 'label=com.shironex.managed=true' 2>/dev/null || true)"
        legacy_ids="$(docker ps -aq --filter 'name=shironex-' 2>/dev/null || true)"
        for container_id in $managed_ids $legacy_ids; do
          [[ -n "$container_id" ]] && docker rm -f "$container_id" >/dev/null 2>&1 || true
        done
        image_ids="$( { docker image ls -q 'itzg/minecraft-server' 2>/dev/null || true; docker image ls -q 'itzg/bungeecord' 2>/dev/null || true; } | sort -u )"
        for image_id in $image_ids; do
          [[ -n "$image_id" ]] && docker rmi -f "$image_id" >/dev/null 2>&1 || true
        done
        volume_ids="$(docker volume ls -q 2>/dev/null | grep -E '^(shironex|snck)-' || true)"
        for volume_id in $volume_ids; do
          [[ -n "$volume_id" ]] && docker volume rm "$volume_id" >/dev/null 2>&1 || true
        done
      fi
      rm -rf -- "$APP_DIR" "$BACKUP_DIR" /etc/snck-panel /var/lib/snck-panel /var/log/snck-panel /opt/shironex-node /etc/shironex-node /var/lib/shironex/servers /var/lib/shironex/.backups /var/lib/snck
      rm -f -- /usr/local/bin/snck-panel /usr/local/bin/shironex-node /usr/local/bin/shironex-node-daemon "$SERVICE_HELPER" /etc/cron.d/snck-panel "$LOG_FILE"
      if id -u snck >/dev/null 2>&1; then userdel snck >/dev/null 2>&1 || true; fi
      systemctl daemon-reload 2>/dev/null || true
      printf '\nAll SNCK components and matched SNCK-managed data have been removed.\n'
      ;;
    *) printf 'Invalid choice; nothing was removed.\n'; return 2 ;;
  esac
}

# Direct command mode: install.sh uninstall or snck-menu.sh uninstall.
case "${1:-}" in
  uninstall|remove|--uninstall|9) uninstall_panel; exit $? ;;
  status) status_panel; exit $? ;;
  restart) restart_panel; exit $? ;;
  logs) logs_panel; exit $? ;;
  backup) backup_panel; exit $? ;;
  install) install_panel; exit $? ;;
  update) update_panel; exit $? ;;
  repair) repair_panel; exit $? ;;
  "") ;;
  *) printf 'Unknown command: %s\\nUsage: %s [install|update|repair|status|restart|logs|backup|uninstall]\\n' "$1" "$0" >&2; exit 2 ;;
esac

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
  printf '  \033[1;97m9)\033[0m Uninstall (Node / Panel / All)\n'
  printf '  \033[1;97m0)\033[0m Exit\n\n'
  read -r -p '  Select an option [0-9]: ' choice </dev/tty || exit 0
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
