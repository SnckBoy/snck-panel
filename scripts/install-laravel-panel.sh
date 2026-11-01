#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="/opt/snck-panel-laravel"
REPO_URL="https://github.com/SnckBoy/snck-panel.git"
BRANCH="php-migration-final"
DRY_RUN=0
for arg in "$@"; do
  case "$arg" in
    --dry-run) DRY_RUN=1;;
    --app-dir=*) APP_DIR="${arg#*=}";;
    --repo=*) REPO_URL="${arg#*=}";;
    --branch=*) BRANCH="${arg#*=}";;
    -h|--help) echo "Usage: $0 [--dry-run] [--app-dir=/opt/snck-panel-laravel] [--repo=URL] [--branch=BRANCH]"; exit 0;;
    *) echo "Unknown option: $arg" >&2; exit 2;;
  esac
done
log(){ printf '[SNCK-LARAVEL] %s\n' "$*"; }
fail(){ log "ERROR: $*" >&2; exit 1; }
run(){ if (( DRY_RUN )); then log "DRY-RUN: $*"; else "$@"; fi; }
(( EUID == 0 )) || fail 'Run as root or with sudo.'
[[ -d /etc ]] || fail 'This installer requires a Linux host.'
source /etc/os-release
case "${ID:-}" in ubuntu|debian) ;; *) fail 'Supported operating systems are Ubuntu and Debian.';; esac
command -v php >/dev/null || fail 'PHP 8.3+ is required.'
php -r 'exit(PHP_VERSION_ID >= 80300 ? 0 : 1);' || fail "PHP 8.3+ is required; found $(php -r 'echo PHP_VERSION;')."
for extension in mbstring openssl pdo_pgsql zip; do php -m | grep -qi "^${extension}$" || fail "PHP extension ${extension} is required."; done
command -v composer >/dev/null || fail 'Composer is required.'
command -v node >/dev/null || fail 'Node.js 20+ is required for asset compilation.'
node -e 'process.exit(Number(process.versions.node.split(".")[0]) >= 20 ? 0 : 1)' || fail 'Node.js 20+ is required.'
command -v npm >/dev/null || fail 'npm is required.'
command -v psql >/dev/null || log 'WARNING: psql is not installed; database connectivity will be checked by Laravel migrations.'
command -v systemctl >/dev/null || log 'WARNING: systemd is unavailable; install will stop before service registration.'
[[ "$APP_DIR" = /* && "$APP_DIR" != / && "$APP_DIR" != */.. ]] || fail 'APP_DIR must be an absolute safe path.'
log "Preflight passed for ${ID:-linux} $(VERSION_ID:-unknown), PHP $(php -r 'echo PHP_VERSION;'), Node $(node --version)."
(( DRY_RUN )) && { log 'Dry-run completed without changing the host.'; exit 0; }
command -v git >/dev/null || fail 'git is required.'
TMP="$(mktemp -d)"; trap 'rm -rf "$TMP"' EXIT
log "Cloning ${REPO_URL} (${BRANCH})..."; git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$TMP/repo"
[[ -f "$TMP/repo/laravel-panel/artisan" ]] || fail 'Repository does not contain laravel-panel/artisan.'
if [[ -e "$APP_DIR" ]]; then cp -a "$APP_DIR" "$APP_DIR.backup.$(date +%Y%m%d%H%M%S)"; fi
mkdir -p "$APP_DIR"; cp -a "$TMP/repo/laravel-panel/." "$APP_DIR/"; cd "$APP_DIR"
[[ -f .env ]] || cp .env.example .env; chmod 600 .env
apt-get update
apt-get install -y nginx php8.3-fpm postgresql-client
php artisan key:generate --force
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
npm ci --no-audit --no-fund && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
id -u snck-panel >/dev/null 2>&1 || useradd --system --home-dir "$APP_DIR" --shell /usr/sbin/nologin snck-panel
chown -R snck-panel:snck-panel "$APP_DIR"
cat >/etc/nginx/sites-available/snck-panel-laravel <<EOF
server {
    listen 80;
    server_name _;
    root $APP_DIR/public;
    index index.php;
    client_max_body_size 512m;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~ /\. { deny all; }
}
EOF
ln -sfn /etc/nginx/sites-available/snck-panel-laravel /etc/nginx/sites-enabled/snck-panel-laravel
rm -f /etc/nginx/sites-enabled/default
nginx -t
cat >/etc/systemd/system/snck-panel-laravel.service <<EOF
[Unit]
Description=SNCK Laravel Panel
After=network-online.target postgresql.service
Wants=network-online.target

[Service]
Type=simple
User=snck-panel
Group=snck-panel
WorkingDirectory=$APP_DIR
EnvironmentFile=$APP_DIR/.env
ExecStart=$(command -v php) $APP_DIR/artisan queue:work --sleep=3 --tries=3 --timeout=120
Restart=always
RestartSec=5
PrivateTmp=true
ProtectHome=true
ReadWritePaths=$APP_DIR/storage $APP_DIR/bootstrap/cache

[Install]
WantedBy=multi-user.target
EOF
cat >/etc/systemd/system/snck-panel-laravel-scheduler.service <<EOF
[Unit]
Description=SNCK Laravel scheduled tasks

[Service]
Type=oneshot
User=snck-panel
Group=snck-panel
WorkingDirectory=$APP_DIR
EnvironmentFile=$APP_DIR/.env
ExecStart=$(command -v php) $APP_DIR/artisan schedule:run
EOF
cat >/etc/systemd/system/snck-panel-laravel-scheduler.timer <<EOF
[Unit]
Description=Run SNCK Laravel scheduler every minute

[Timer]
OnBootSec=1min
OnUnitActiveSec=1min
Unit=snck-panel-laravel-scheduler.service

[Install]
WantedBy=timers.target
EOF
systemctl daemon-reload; systemctl enable --now nginx php8.3-fpm snck-panel-laravel.service snck-panel-laravel-scheduler.timer
log 'CLI installation completed. The web installer is available at /setup for first-owner creation.'
