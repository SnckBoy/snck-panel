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
NODE_VERSION="${SNCK_NODE_VERSION:-22.14.0}"
SERVICE_HELPER="/usr/local/bin/snck-panel-service"
HAS_SYSTEMD=0
if command -v systemctl >/dev/null 2>&1 && [[ -d /run/systemd/system ]] && systemctl show-environment >/dev/null 2>&1; then HAS_SYSTEMD=1; fi

[[ "$PORT" =~ ^[0-9]{1,5}$ ]] && (( PORT >= 1 && PORT <= 65535 )) || { printf '[SNCK] ERROR: SNCK_PORT must be an integer from 1 to 65535.\n' >&2; exit 1; }

log(){ printf '[SNCK] %s\n' "$*" | tee -a "$LOG_FILE"; }
die(){ log "ERROR: $*"; exit 1; }

TMP=""
cleanup(){ [[ -z "$TMP" ]] || rm -rf "$TMP"; }
trap 'rc=$?; cleanup; if [[ $rc -ne 0 ]]; then log "Installation failed with exit code $rc. See $LOG_FILE"; fi' EXIT

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
apt-get install -y ca-certificates curl git openssl build-essential xz-utils >>"$LOG_FILE" 2>&1
if (( HAS_SYSTEMD == 0 )); then
  log "systemd is not running; preparing PM2 + cron service mode..."
  apt-get install -y cron >>"$LOG_FILE" 2>&1 || die "Could not install cron for reboot persistence on this non-systemd host."
  if command -v systemctl >/dev/null 2>&1; then systemctl enable --now cron.service >>"$LOG_FILE" 2>&1 || true; fi
  if ! pgrep -x cron >/dev/null 2>&1; then service cron start >>"$LOG_FILE" 2>&1 || die "Neither systemd nor a working cron service is available. Start this installer on a full VPS/host with a supported service manager."; fi
  pgrep -x cron >/dev/null 2>&1 || die "Cron could not be started; reboot persistence cannot be configured."
fi

# NodeSource is not required. Some VPS networks/firewalls block deb.nodesource.com,
# while GitHub is reachable. Prefer an existing Node >=20, otherwise install the
# pinned official Node 22 binary with a GitHub release fallback.
node_major=0
if command -v node >/dev/null 2>&1; then
  node_major="$(node -p 'Number(process.versions.node.split(".")[0])' 2>/dev/null || echo 0)"
fi

if (( node_major < 20 )); then
  log "Installing Node.js ${NODE_VERSION} without NodeSource..."
  arch="$(dpkg --print-architecture)"
  case "$arch" in
    amd64) node_arch="x64" ;;
    arm64) node_arch="arm64" ;;
    *) die "Unsupported architecture for Node.js: $arch" ;;
  esac

  node_file="node-v${NODE_VERSION}-linux-${node_arch}.tar.xz"
  node_tmp="/tmp/${node_file}"
  # Try multiple independent mirrors. Some cloud networks block nodejs.org and
  # GitHub release assets even when general HTTPS access works.
  node_urls=(
    "https://nodejs.org/dist/v${NODE_VERSION}/${node_file}"
    "https://npmmirror.com/mirrors/node/v${NODE_VERSION}/${node_file}"
    "https://mirrors.nju.edu.cn/nodejs-release/v${NODE_VERSION}/${node_file}"
    "https://mirrors.tuna.tsinghua.edu.cn/nodejs-release/v${NODE_VERSION}/${node_file}"
    "https://github.com/nodejs/node/releases/download/v${NODE_VERSION}/${node_file}"
  )
  downloaded=0
  for node_url in "${node_urls[@]}"; do
    log "Trying Node.js download mirror: ${node_url}"
    rm -f "${node_tmp}"
    if curl -fL --retry 1 --connect-timeout 10 --max-time 120 "${node_url}" -o "${node_tmp}" >>"${LOG_FILE}" 2>&1 &&
       [[ -s "${node_tmp}" ]] &&
       xz -t "${node_tmp}" >>"${LOG_FILE}" 2>&1; then
      downloaded=1
      break
    fi
    log "This Node.js mirror failed; trying the next one..."
  done
  (( downloaded == 1 )) || die "Could not download a valid Node.js ${NODE_VERSION} archive from any configured mirror. Check DNS/firewall access and /var/log/snck-panel-installer.log."

  rm -rf "/opt/node-v${NODE_VERSION}"
  mkdir -p "/opt/node-v${NODE_VERSION}"
  tar -xJf "${node_tmp}" --strip-components=1 -C "/opt/node-v${NODE_VERSION}" >>"${LOG_FILE}" 2>&1 || die "Downloaded Node.js archive could not be extracted."
  rm -f "${node_tmp}"
  ln -sfn "/opt/node-v${NODE_VERSION}" /opt/node
  ln -sfn /opt/node/bin/node /usr/local/bin/node
  ln -sfn /opt/node/bin/npm /usr/local/bin/npm
  ln -sfn /opt/node/bin/npx /usr/local/bin/npx
  hash -r 2>/dev/null || true
fi

command -v node >/dev/null 2>&1 || die "Node.js installation failed."
command -v npm >/dev/null 2>&1 || die "npm installation failed."
node_major="$(node -p 'Number(process.versions.node.split(".")[0])')"
(( node_major >= 20 )) || die "Node.js 20 or newer is required; found $(node --version)."
log "Using Node.js $(node --version) and npm $(npm --version)."

if ! command -v docker >/dev/null 2>&1; then
  log "Installing Docker..."
  curl -fsSL --retry 3 --connect-timeout 10 https://get.docker.com -o /tmp/get-docker.sh
  sh /tmp/get-docker.sh >>"$LOG_FILE" 2>&1
  rm -f /tmp/get-docker.sh
fi
command -v docker >/dev/null 2>&1 || die "Docker installation failed."
if ! docker info >/dev/null 2>&1; then
  if (( HAS_SYSTEMD == 1 )); then
    systemctl enable --now docker >>"$LOG_FILE" 2>&1 || die "Docker service could not be started."
  else
    service docker start >>"$LOG_FILE" 2>&1 || die "Docker daemon could not be started without systemd. Confirm this VPS supports Docker and its init scripts."
  fi
fi
docker info >>"$LOG_FILE" 2>&1 || die "Docker is installed but not operational. SNCK Panel requires a working Docker daemon."

# The current application uses its JSON file store (.data); do not provision an
# unused PostgreSQL database or run a one-way migration during installation.

# Keep one current rollback backup instead of accumulating stale copies on
# every update. Never allow a malformed or root backup path to be deleted.
if [[ "$BACKUP_DIR" == "/" || -z "$BACKUP_DIR" || "$BACKUP_DIR" != /* ]]; then
  die "Refusing to use an unsafe backup directory: $BACKUP_DIR"
fi
mkdir -p "$BACKUP_DIR"
log "Keeping existing backups; creating a fresh rollback backup..."
timestamp="$(date +%Y%m%d-%H%M%S)"
backup="$BACKUP_DIR/$timestamp"
mkdir -p "$backup"
chmod 700 "$BACKUP_DIR" "$backup"
if [[ -d "$APP_DIR/.data" ]]; then cp -a "$APP_DIR/.data" "$backup/.data"; fi
if [[ -f "$APP_DIR/.env" ]]; then cp -a "$APP_DIR/.env" "$backup/.env"; fi
log "Backup created: $backup"

TMP="$(mktemp -d /tmp/snck-panel-install.XXXXXX)"

log "Fetching SNCK PANEL source..."
git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$TMP/repo" >>"$LOG_FILE" 2>&1 || die "Could not download SNCK PANEL from $REPO_URL."
[[ -f "$TMP/repo/package.json" ]] || die "Downloaded repository is not a valid SNCK PANEL source tree."

mkdir -p "$APP_DIR"
if [[ -x "$SERVICE_HELPER" ]]; then "$SERVICE_HELPER" stop >>"$LOG_FILE" 2>&1 || true
elif (( HAS_SYSTEMD == 1 )) && systemctl is-active --quiet "$SERVICE" 2>/dev/null; then systemctl stop "$SERVICE" >>"$LOG_FILE" 2>&1 || true; fi
find "$APP_DIR" -mindepth 1 -maxdepth 1 ! -name .data ! -name .env -exec rm -rf -- {} +
cp -a "$TMP/repo"/. "$APP_DIR"/
cd "$APP_DIR"

[[ -f .env ]] || touch .env
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
npm ci --include=dev --no-audit --no-fund >>"$LOG_FILE" 2>&1 || die "Locked dependency installation failed. Check package-lock.json and the installer log before retrying."
log "Preparing the JSON data store..."
install -d -o root -g root -m 750 "$APP_DIR/.data"
install -d -o root -g root -m 750 "$APP_DIR/.data/themes"
[[ -f .data/users.json ]] || printf '[]\n' > .data/users.json
[[ -f .data/servers.json ]] || printf '[]\n' > .data/servers.json
[[ -f .data/settings.json ]] || printf '{}\n' > .data/settings.json

log "Running TypeScript validation..."
if ! npm run lint >>"$LOG_FILE" 2>&1; then
  log "TypeScript validation failed. Showing the compiler diagnostics:"
  tail -n 120 "$LOG_FILE" >&2 || true
  die "TypeScript validation failed. The compiler output is shown above and saved in $LOG_FILE."
fi
log "Building frontend and backend..."
if ! npm run build >>"$LOG_FILE" 2>&1; then
  log "Application build failed. Showing the build diagnostics:"
  tail -n 120 "$LOG_FILE" >&2 || true
  die "Application build failed. The build output is shown above and saved in $LOG_FILE."
fi
[[ -f dist/server.cjs ]] || die "Backend build did not produce dist/server.cjs."

id -u snck >/dev/null 2>&1 || useradd --system --home-dir "$APP_DIR" --shell /usr/sbin/nologin snck
chown -R snck:snck "$APP_DIR"
chmod 750 "$APP_DIR/.data"
chmod 750 "$APP_DIR/.data/themes"
find "$APP_DIR/.data" -maxdepth 1 -type f -name '*.json' -exec chmod 640 {} +
if getent group docker >/dev/null 2>&1; then usermod -aG docker snck || die "Could not grant SNCK service user Docker access."; fi
mkdir -p /var/lib/snck
chown snck:snck /var/lib/snck
install -d -o snck -g snck -m 750 /var/lib/snck/pm2
install -o root -g root -m 755 "$APP_DIR/scripts/panel-service.sh" "$SERVICE_HELPER"

if (( HAS_SYSTEMD == 1 )); then
cat >"/etc/systemd/system/$SERVICE" <<EOF
[Unit]
Description=SNCK PANEL
After=network-online.target docker.service
Wants=network-online.target
Requires=docker.service

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
PrivateTmp=true
ProtectHome=true
ReadWritePaths=$APP_DIR /var/lib/snck
LimitNOFILE=65535

[Install]
WantedBy=multi-user.target
EOF
  systemctl daemon-reload
  "$SERVICE_HELPER" boot-install >>"$LOG_FILE" 2>&1
else
  "$SERVICE_HELPER" boot-install >>"$LOG_FILE" 2>&1 || die "Could not configure panel startup at reboot through cron."
fi
"$SERVICE_HELPER" restart >>"$LOG_FILE" 2>&1
sleep 3
if (( HAS_SYSTEMD == 1 )); then systemctl is-active --quiet "$SERVICE" || { journalctl -u "$SERVICE" -n 80 --no-pager; die "SNCK PANEL service did not start."; }
else "$SERVICE_HELPER" status >>"$LOG_FILE" 2>&1 || { tail -n 80 "$LOG_FILE" >&2; die "SNCK PANEL PM2 process did not start."; }
fi

if curl -fsS --max-time 10 "http://127.0.0.1:$PORT/health" >/dev/null 2>&1; then
  log "Health check passed."
else
  "$SERVICE_HELPER" logs 80 || true
  die "SNCK PANEL started but /health did not respond on port $PORT."
fi

log "SNCK PANEL installation/update completed successfully."
printf '\nSNCK PANEL\n  Directory: %s\n  Service:   %s\n  Port:      %s\n  Storage:   %s/.data\n  Backup:    %s\n\n' "$APP_DIR" "$SERVICE" "$PORT" "$APP_DIR" "$backup"
printf 'Open:   http://SERVER-IP:%s\n' "$PORT"
if (( HAS_SYSTEMD == 1 )); then printf 'Status: systemctl status %s --no-pager\nLogs:   journalctl -u %s -f\n' "$SERVICE" "$SERVICE"; else printf 'Status: %s status\nLogs:   %s logs\n' "$SERVICE_HELPER" "$SERVICE_HELPER"; fi
printf 'Config: %s/.env\n' "$APP_DIR"
