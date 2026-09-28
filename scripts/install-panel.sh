#!/usr/bin/env bash
set -Eeuo pipefail

APP_NAME="snck-panel"
APP_DIR="${SNCK_PANEL_DIR:-/opt/snck-panel}"
REPO_URL="${SNCK_REPO_URL:-https://github.com/SnckBoy/snck-panel.git}"
BRANCH="${SNCK_BRANCH:-main}"
PORT="${SNCK_PORT:-6767}"
SERVICE="snck-panel.service"
BACKUP_DIR="${SNCK_BACKUP_DIR:-/var/backups/snck-panel}"
LOG_FILE="/var/log/snck-panel-installer.log"

log(){ printf '[SNCK] %s\n' "$*" | tee -a "$LOG_FILE"; }
die(){ log "ERROR: $*"; exit 1; }
trap 'rc=$?; if [[ $rc -ne 0 ]]; then log "Installation failed with exit code $rc. See $LOG_FILE"; fi' EXIT

[[ "$EUID" -eq 0 ]] || die "Run with sudo/root."
mkdir -p "$(dirname "$LOG_FILE")"
touch "$LOG_FILE"
chmod 600 "$LOG_FILE"

[[ -r /etc/os-release ]] || die "Cannot detect operating system."
. /etc/os-release
case "$ID" in
  ubuntu)
    dpkg --compare-versions "${VERSION_ID:-0}" ge 20.04 || die "Ubuntu 20.04 or newer is required."
    ;;
  debian)
    case "${VERSION_ID:-}" in 11|12|13) ;; *) die "Debian 11, 12, or 13 is required." ;; esac
    ;;
  *) die "Supported operating systems: Ubuntu 20.04+ or Debian 11/12/13." ;;
esac
case "$(dpkg --print-architecture)" in amd64|arm64) ;; *) die "Supported architectures: amd64 and arm64." ;; esac

export DEBIAN_FRONTEND=noninteractive
log "Installing system prerequisites..."
apt-get update >>"$LOG_FILE" 2>&1
apt-get install -y ca-certificates curl git openssl build-essential >>"$LOG_FILE" 2>&1

if ! command -v node >/dev/null 2>&1 || [[ "$(node -p 'Number(process.versions.node.split(".")[0])')" -lt 20 ]]; then
  log "Installing Node.js 22..."
  curl -fsSL https://deb.nodesource.com/setup_22.x | bash - >>"$LOG_FILE" 2>&1 || true
  apt-get install -y nodejs >>"$LOG_FILE" 2>&1 || true
fi
command -v node >/dev/null 2>&1 || die "Node.js installation failed."
command -v npm >/dev/null 2>&1 || die "npm installation failed."

if ! command -v docker >/dev/null 2>&1; then
  log "Installing Docker..."
  curl -fsSL https://get.docker.com | sh >>"$LOG_FILE" 2>&1 || die "Docker installation failed."
fi
if command -v systemctl >/dev/null 2>&1 && [[ -d /run/systemd/system ]]; then
  systemctl enable --now docker >>"$LOG_FILE" 2>&1 || die "Docker service could not be started."
fi

timestamp="$(date +%Y%m%d-%H%M%S)"
backup="$BACKUP_DIR/$timestamp"
mkdir -p "$backup"
chmod 700 "$BACKUP_DIR" "$backup"
if [[ -d "$APP_DIR/.data" ]]; then cp -a "$APP_DIR/.data" "$backup/.data"; fi
if [[ -f "$APP_DIR/.env" ]]; then cp -a "$APP_DIR/.env" "$backup/.env"; fi
log "Backup created: $backup"

TMP="$(mktemp -d /tmp/snck-panel-install.XXXXXX)"
cleanup(){ rm -rf "$TMP"; }
trap cleanup EXIT

log "Fetching SNCK PANEL source..."
git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$TMP/repo" >>"$LOG_FILE" 2>&1 || die "Could not download SNCK PANEL from $REPO_URL."
[[ -f "$TMP/repo/package.json" ]] || die "Downloaded repository is not a valid SNCK PANEL source tree."

mkdir -p "$APP_DIR"
# Preserve runtime state while replacing application code with the requested branch.
find "$APP_DIR" -mindepth 1 -maxdepth 1 ! -name .data ! -name .env -exec rm -rf -- {} +
cp -a "$TMP/repo"/. "$APP_DIR"/
cd "$APP_DIR"

if [[ ! -f .env ]]; then
  touch .env
fi
chmod 600 .env
set_env(){
  local key="$1" value="$2"
  if grep -q "^${key}=" .env; then
    sed -i "s#^${key}=.*#${key}=\"${value//\\/\\\\}\"#" .env
  else
    printf '%s="%s"\n' "$key" "$value" >> .env
  fi
}
set_env NODE_ENV production
set_env PORT "$PORT"
if ! grep -q '^JWT_SECRET=' .env; then printf 'JWT_SECRET="%s"\n' "$(openssl rand -hex 32)" >> .env; fi
if ! grep -q '^NODE_AUTH_SECRET=' .env; then printf 'NODE_AUTH_SECRET="%s"\n' "$(openssl rand -hex 32)" >> .env; fi
if ! grep -q '^NODE_ENCRYPTION_KEY=' .env; then printf 'NODE_ENCRYPTION_KEY="%s"\n' "$(openssl rand -hex 32)" >> .env; fi

log "Installing Node dependencies..."
npm ci --no-audit --no-fund >>"$LOG_FILE" 2>&1 || npm install --no-audit --no-fund >>"$LOG_FILE" 2>&1
log "Running TypeScript validation..."
npm run lint >>"$LOG_FILE" 2>&1
log "Building frontend and backend..."
npm run build >>"$LOG_FILE" 2>&1

id -u snck >/dev/null 2>&1 || useradd --system --home-dir "$APP_DIR" --shell /usr/sbin/nologin snck
chown -R snck:snck "$APP_DIR"
if getent group docker >/dev/null 2>&1; then usermod -aG docker snck || true; fi

cat >"/etc/systemd/system/$SERVICE" <<EOF
[Unit]
Description=SNCK PANEL
After=network-online.target docker.service
Wants=network-online.target

[Service]
Type=simple
User=snck
Group=snck
WorkingDirectory=$APP_DIR
EnvironmentFile=$APP_DIR/.env
Environment=NODE_ENV=production
ExecStart=$(command -v node) $APP_DIR/dist/server.cjs
Restart=always
RestartSec=5
TimeoutStopSec=30
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=full
ProtectHome=true
ReadWritePaths=$APP_DIR/.data /var/lib/snck
LimitNOFILE=65535

[Install]
WantedBy=multi-user.target
EOF

mkdir -p /var/lib/snck
chown snck:snck /var/lib/snck
systemctl daemon-reload
systemctl enable "$SERVICE" >>"$LOG_FILE" 2>&1
systemctl restart "$SERVICE" >>"$LOG_FILE" 2>&1
sleep 3
systemctl is-active --quiet "$SERVICE" || { journalctl -u "$SERVICE" -n 80 --no-pager; die "SNCK PANEL service did not start."; }

if command -v curl >/dev/null 2>&1; then
  if curl -fsS --max-time 10 "http://127.0.0.1:$PORT/health" >/dev/null 2>&1; then
    log "Health check passed."
  else
    journalctl -u "$SERVICE" -n 40 --no-pager || true
    die "SNCK PANEL started but /health did not respond on port $PORT."
  fi
fi

log "SNCK PANEL installation/update completed successfully."
printf '\nSNCK PANEL\n  Directory: %s\n  Service:   %s\n  Port:      %s\n  Backup:    %s\n\n' "$APP_DIR" "$SERVICE" "$PORT" "$backup"
printf 'Open: http://SERVER-IP:%s\n' "$PORT"
printf 'Status: systemctl status %s --no-pager\n' "$SERVICE"
printf 'Logs:   journalctl -u %s -f\n' "$SERVICE"
