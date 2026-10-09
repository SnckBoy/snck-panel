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
DB_NAME="${SNCK_DB_NAME:-snck_panel}"
DB_USER="${SNCK_DB_USER:-snck_panel}"
DB_HOST="${SNCK_DB_HOST:-127.0.0.1}"
DB_PORT="${SNCK_DB_PORT:-5432}"
NODE_VERSION="${SNCK_NODE_VERSION:-22.14.0}"

log(){ printf '[SNCK] %s\n' "$*" | tee -a "$LOG_FILE"; }
die(){ log "ERROR: $*"; exit 1; }

# Validate values that are later embedded in PostgreSQL commands or connection URLs.
# The default password is generated as hex; custom passwords must remain URL/SQL-safe.
[[ "$DB_NAME" =~ ^[A-Za-z_][A-Za-z0-9_]{0,62}$ ]] || die "SNCK_DB_NAME must start with a letter/underscore and contain only letters, digits, or underscores (max 63 chars)."
[[ "$DB_USER" =~ ^[A-Za-z_][A-Za-z0-9_]{0,62}$ ]] || die "SNCK_DB_USER must start with a letter/underscore and contain only letters, digits, or underscores (max 63 chars)."
[[ "$DB_HOST" =~ ^[A-Za-z0-9._:-]+$ ]] || die "SNCK_DB_HOST contains unsupported characters."
[[ "$DB_PORT" =~ ^[0-9]{1,5}$ ]] && (( DB_PORT >= 1 && DB_PORT <= 65535 )) || die "SNCK_DB_PORT must be an integer from 1 to 65535."
if [[ -n "${SNCK_DB_PASSWORD:-}" ]]; then
  [[ "$SNCK_DB_PASSWORD" =~ ^[A-Za-z0-9_-]{32,128}$ ]] || die "SNCK_DB_PASSWORD must be 32-128 characters using letters, digits, underscore, or hyphen."
fi

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
apt-get install -y ca-certificates curl git openssl build-essential postgresql postgresql-client xz-utils >>"$LOG_FILE" 2>&1

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
  node_url_primary="https://nodejs.org/dist/v${NODE_VERSION}/${node_file}"
  node_url_fallback="https://github.com/nodejs/node/releases/download/v${NODE_VERSION}/${node_file}"

  if ! curl -fL --retry 2 --connect-timeout 8 --max-time 60 "$node_url_primary" -o "$node_tmp" >>"$LOG_FILE" 2>&1; then
    log "nodejs.org is unreachable; trying GitHub release mirror..."
    curl -fL --retry 3 --connect-timeout 8 --max-time 120 "$node_url_fallback" -o "$node_tmp" >>"$LOG_FILE" 2>&1 || die "Could not download Node.js ${NODE_VERSION}. Your VPS network is blocking both Node.js download endpoints."
  fi

  rm -rf /opt/node-v${NODE_VERSION}
  mkdir -p /opt/node-v${NODE_VERSION}
  tar -xJf "$node_tmp" -C /opt
  rm -f "$node_tmp"
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
if command -v systemctl >/dev/null 2>&1 && [[ -d /run/systemd/system ]]; then
  systemctl enable --now docker >>"$LOG_FILE" 2>&1 || die "Docker service could not be started."
  docker info >>"$LOG_FILE" 2>&1 || die "Docker is installed but not operational."
fi

if command -v systemctl >/dev/null 2>&1 && [[ -d /run/systemd/system ]]; then
  systemctl enable --now postgresql >>"$LOG_FILE" 2>&1 || die "PostgreSQL service could not be started."
fi
command -v psql >/dev/null 2>&1 || die "PostgreSQL client installation failed."

if [[ -z "${SNCK_DB_PASSWORD:-}" ]]; then
  SNCK_DB_PASSWORD="$(openssl rand -hex 32)"
fi
export PGPASSWORD=""
if ! runuser -u postgres -- psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='${DB_USER//'/''}'" | grep -qx 1; then
  runuser -u postgres -- psql -v ON_ERROR_STOP=1 -c "CREATE ROLE \"$DB_USER\" LOGIN PASSWORD '$SNCK_DB_PASSWORD';" >>"$LOG_FILE" 2>&1 || die "Could not create PostgreSQL role."
else
  runuser -u postgres -- psql -v ON_ERROR_STOP=1 -c "ALTER ROLE \"$DB_USER\" WITH LOGIN PASSWORD '$SNCK_DB_PASSWORD';" >>"$LOG_FILE" 2>&1 || die "Could not update PostgreSQL role."
fi
if ! runuser -u postgres -- psql -tAc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME//'/'}'" | grep -qx 1; then
  runuser -u postgres -- createdb -O "$DB_USER" "$DB_NAME" >>"$LOG_FILE" 2>&1 || die "Could not create PostgreSQL database."
else
  runuser -u postgres -- psql -v ON_ERROR_STOP=1 -c "ALTER DATABASE \"$DB_NAME\" OWNER TO \"$DB_USER\";" >>"$LOG_FILE" 2>&1 || die "Could not verify PostgreSQL database ownership."
fi
DATABASE_URL="postgresql://${DB_USER}:${SNCK_DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_NAME}"

PGPASSWORD="$SNCK_DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -v ON_ERROR_STOP=1 -c 'SELECT 1;' >>"$LOG_FILE" 2>&1 || die "PostgreSQL connection test failed."

mkdir -p "$BACKUP_DIR"
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
[[ -f "$TMP/repo/database/schema.sql" ]] || die "Database schema is missing from the downloaded source tree."

mkdir -p "$APP_DIR"
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
set_env DATABASE_URL "$DATABASE_URL"
set_env PGHOST "$DB_HOST"
set_env PGPORT "$DB_PORT"
set_env PGDATABASE "$DB_NAME"
set_env PGUSER "$DB_USER"
if ! grep -q '^JWT_SECRET=' .env; then printf 'JWT_SECRET="%s"\n' "$(openssl rand -hex 32)" >> .env; fi
if ! grep -q '^NODE_AUTH_SECRET=' .env; then printf 'NODE_AUTH_SECRET="%s"\n' "$(openssl rand -hex 32)" >> .env; fi
if ! grep -q '^NODE_ENCRYPTION_KEY=' .env; then printf 'NODE_ENCRYPTION_KEY="%s"\n' "$(openssl rand -hex 32)" >> .env; fi

log "Installing Node dependencies..."
npm ci --no-audit --no-fund >>"$LOG_FILE" 2>&1 || { log "npm ci failed; retrying with npm install..."; npm install --no-audit --no-fund >>"$LOG_FILE" 2>&1 || die "Node dependency installation failed."; }
log "Applying PostgreSQL schema..."
PGPASSWORD="$SNCK_DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -v ON_ERROR_STOP=1 -f database/schema.sql >>"$LOG_FILE" 2>&1 || die "Database schema installation failed."

if [[ -d .data ]] && compgen -G '.data/*.json' >/dev/null 2>&1; then
  log "Legacy JSON data detected; running transactional migration..."
  DATABASE_URL="$DATABASE_URL" SNCK_DATA_DIR="$APP_DIR/.data" npm run migrate:json >>"$LOG_FILE" 2>&1 || die "Legacy JSON migration failed; application update was not started."
fi

log "Running TypeScript validation..."
npm run lint >>"$LOG_FILE" 2>&1 || die "TypeScript validation failed."
log "Building frontend and backend..."
npm run build >>"$LOG_FILE" 2>&1 || die "Application build failed."
[[ -f dist/server.cjs ]] || die "Backend build did not produce dist/server.cjs."

id -u snck >/dev/null 2>&1 || useradd --system --home-dir "$APP_DIR" --shell /usr/sbin/nologin snck
chown -R snck:snck "$APP_DIR"
if getent group docker >/dev/null 2>&1; then usermod -aG docker snck || die "Could not grant SNCK service user Docker access."; fi
mkdir -p /var/lib/snck
chown snck:snck /var/lib/snck

if ! command -v systemctl >/dev/null 2>&1 || [[ ! -d /run/systemd/system ]]; then
  die "systemd is required for the production installer."
fi

cat >"/etc/systemd/system/$SERVICE" <<EOF
[Unit]
Description=SNCK PANEL
After=network-online.target docker.service postgresql.service
Wants=network-online.target
Requires=postgresql.service

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
systemctl enable "$SERVICE" >>"$LOG_FILE" 2>&1
systemctl restart "$SERVICE" >>"$LOG_FILE" 2>&1
sleep 3
systemctl is-active --quiet "$SERVICE" || { journalctl -u "$SERVICE" -n 80 --no-pager; die "SNCK PANEL service did not start."; }

if curl -fsS --max-time 10 "http://127.0.0.1:$PORT/health" >/dev/null 2>&1; then
  log "Health check passed."
else
  journalctl -u "$SERVICE" -n 80 --no-pager || true
  die "SNCK PANEL started but /health did not respond on port $PORT."
fi

log "SNCK PANEL installation/update completed successfully."
printf '\nSNCK PANEL\n  Directory: %s\n  Service:   %s\n  Port:      %s\n  Database:  %s@%s:%s/%s\n  Backup:    %s\n\n' "$APP_DIR" "$SERVICE" "$PORT" "$DB_USER" "$DB_HOST" "$DB_PORT" "$DB_NAME" "$backup"
printf 'Open:   http://SERVER-IP:%s\n' "$PORT"
printf 'Status: systemctl status %s --no-pager\n' "$SERVICE"
printf 'Logs:   journalctl -u %s -f\n' "$SERVICE"
printf 'Config: %s/.env\n' "$APP_DIR"
