#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="${SNCK_PANEL_DIR:-/opt/snck-panel-laravel}"
REPO_URL="${SNCK_REPO_URL:-https://github.com/SnckBoy/snck-panel.git}"
BRANCH="${SNCK_BRANCH:-main}"
DRY_RUN=0

for arg in "$@"; do
  case "$arg" in
    --dry-run) DRY_RUN=1 ;;
    --app-dir=*) APP_DIR="${arg#*=}" ;;
    --repo=*) REPO_URL="${arg#*=}" ;;
    --branch=*) BRANCH="${arg#*=}" ;;
    -h|--help)
      cat <<'EOF'
SNCK Panel — Laravel installer

Usage:
  sudo bash scripts/install-laravel-panel.sh [options]

Options:
  --dry-run
  --app-dir=/opt/snck-panel-laravel
  --repo=https://github.com/SnckBoy/snck-panel.git
  --branch=main
  --help

Prerequisites:
  PHP 8.3+, Composer, Node.js 20+, npm, and a reachable PostgreSQL database.
  The installer installs Nginx, PHP-FPM 8.3 and the PostgreSQL client.
EOF
      exit 0
      ;;
    *) echo "Unknown option: $arg" >&2; exit 2 ;;
  esac
done

log(){ printf '[SNCK-LARAVEL] %s\n' "$*"; }
fail(){ log "ERROR: $*" >&2; exit 1; }
run(){ if (( DRY_RUN )); then log "DRY-RUN: $*"; else "$@"; fi; }

(( EUID == 0 )) || fail 'Run as root or with sudo.'
[[ -r /etc/os-release ]] || fail 'Cannot detect the operating system.'
. /etc/os-release
case "${ID:-}" in
  ubuntu|debian) ;;
  *) fail 'Supported operating systems are Ubuntu and Debian.' ;;
esac
[[ -d /run/systemd/system ]] || fail 'A systemd-based Ubuntu/Debian host is required.'

command -v php >/dev/null || fail 'PHP 8.3+ is required.'
php -r 'exit(PHP_VERSION_ID >= 80300 ? 0 : 1);' || fail "PHP 8.3+ is required; found $(php -r 'echo PHP_VERSION;')."
for extension in mbstring openssl pdo_pgsql zip; do
  php -m | grep -qi "^$extension$" || fail "PHP extension $extension is required."
done
command -v composer >/dev/null || fail 'Composer is required.'
command -v node >/dev/null || fail 'Node.js 20+ is required for asset compilation.'
node -e 'process.exit(Number(process.versions.node.split(".")[0]) >= 20 ? 0 : 1)' || fail 'Node.js 20+ is required.'
command -v npm >/dev/null || fail 'npm is required.'
command -v git >/dev/null || fail 'git is required.'
command -v apt-get >/dev/null || fail 'apt-get is required.'
command -v systemctl >/dev/null || fail 'systemd is required.'

[[ "$APP_DIR" = /* && "$APP_DIR" != / ]] || fail 'APP_DIR must be an absolute, non-root path.'
case "/$APP_DIR/" in */../*) fail 'APP_DIR must not contain parent-directory segments.' ;; esac
[[ "$APP_DIR" != /opt && "$APP_DIR" != /var && "$APP_DIR" != /usr && "$APP_DIR" != /etc ]] || fail 'APP_DIR must be a dedicated application directory.'
[[ "$REPO_URL" == https://* || "$REPO_URL" == ssh://* || "$REPO_URL" == git@* ]] || fail 'REPO_URL must use HTTPS or SSH.'
[[ -n "$BRANCH" ]] || fail 'Branch cannot be empty.'

log "Preflight passed for ${PRETTY_NAME:-$ID}; PHP $(php -r 'echo PHP_VERSION;'), Node $(node --version)."
(( DRY_RUN )) && { log 'Dry-run completed without changing the host.'; exit 0; }

TMP="$(mktemp -d /tmp/snck-panel-install.XXXXXX)"
trap 'rm -rf -- "$TMP"' EXIT
log "Cloning $REPO_URL ($BRANCH)..."
git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$TMP/repo"
SOURCE="$TMP/repo/laravel-panel"
[[ -f "$SOURCE/artisan" && -f "$SOURCE/composer.json" && -f "$SOURCE/.env.example" ]] || fail 'Selected branch does not contain a complete laravel-panel application.'

# Keep a rollback copy, then replace the application tree cleanly.
if [[ -e "$APP_DIR" ]]; then
  [[ -d "$APP_DIR" && ! -L "$APP_DIR" ]] || fail 'Existing APP_DIR must be a real directory, not a symlink or file.'
  BACKUP="$APP_DIR.backup.$(date +%Y%m%d%H%M%S)"
  log "Backing up existing application to $BACKUP"
  cp -a -- "$APP_DIR" "$BACKUP"
  rm -rf -- "$APP_DIR"
fi
install -d -m 755 "$APP_DIR"
cp -a "$SOURCE/." "$APP_DIR/"
cd "$APP_DIR"

[[ -f .env ]] || cp .env.example .env
chmod 600 .env

# Collect database settings before migrations. Values may be supplied through
# SNCK_DB_* for automated deployments, otherwise the operator is prompted.
prompt_value() {
  local variable="$1" prompt="$2" default="$3" current answer=""
  current="${!variable:-$default}"
  if [[ -r /dev/tty ]]; then
    read -r -p "$prompt [$current]: " answer </dev/tty || true
    printf '%s' "${answer:-$current}"
  else
    [[ -n "$current" ]] || fail "$variable must be set for non-interactive installation."
    printf '%s' "$current"
  fi
}
DB_HOST="$(prompt_value SNCK_DB_HOST 'PostgreSQL host' '127.0.0.1')"
DB_PORT="$(prompt_value SNCK_DB_PORT 'PostgreSQL port' '5432')"
DB_DATABASE="$(prompt_value SNCK_DB_DATABASE 'PostgreSQL database' 'snck_panel')"
DB_USERNAME="$(prompt_value SNCK_DB_USERNAME 'PostgreSQL username' 'snck_panel')"
if [[ -n "${SNCK_DB_PASSWORD:-}" ]]; then
  DB_PASSWORD="$SNCK_DB_PASSWORD"
elif [[ -r /dev/tty ]]; then
  read -r -s -p 'PostgreSQL password: ' DB_PASSWORD </dev/tty || fail 'Could not read the PostgreSQL password.'
  printf '\\n'
else
  fail 'Set SNCK_DB_PASSWORD for non-interactive installation.'
fi
[[ "$DB_PORT" =~ ^[0-9]+$ && "$DB_PORT" -ge 1 && "$DB_PORT" -le 65535 ]] || fail 'Invalid PostgreSQL port.'
[[ -n "$DB_HOST" && -n "$DB_DATABASE" && -n "$DB_USERNAME" && -n "$DB_PASSWORD" ]] || fail 'Database settings cannot be empty.'

APP_URL="$(prompt_value SNCK_APP_URL 'Public panel URL (set HTTPS after configuring TLS)' 'http://localhost')"
DB_SSLMODE="${SNCK_DB_SSLMODE:-prefer}"

# Safely replace dotenv values without evaluating or sourcing .env.
set_env() {
  local key="$1" value="$2"
  [[ "$value" != *$'\n'* && "$value" != *$'\r'* ]] || fail "$key cannot contain a newline."
  SNCK_ENV_KEY="$key" SNCK_ENV_VALUE="$value" php -r '
    $path = ".env";
    $key = getenv("SNCK_ENV_KEY");
    $value = getenv("SNCK_ENV_VALUE");
    $quoted = "\"" . str_replace(["\\", "\""], ["\\\\", "\\\""], $value) . "\"";
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) { fwrite(STDERR, "Cannot read .env\n"); exit(1); }
    $prefix = $key . "=";
    $updated = false;
    foreach ($lines as &$line) {
      if (str_starts_with($line, $prefix)) {
        $line = $prefix . $quoted;
        $updated = true;
      }
    }
    unset($line);
    if (!$updated) { $lines[] = $prefix . $quoted; }
    if (file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX) === false) {
      fwrite(STDERR, "Cannot write .env\n"); exit(1);
    }
  ' || fail "Could not update $key in .env."
}
set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "$APP_URL"
set_env DB_CONNECTION pgsql
set_env DB_HOST "$DB_HOST"
set_env DB_PORT "$DB_PORT"
set_env DB_DATABASE "$DB_DATABASE"
set_env DB_USERNAME "$DB_USERNAME"
set_env DB_PASSWORD "$DB_PASSWORD"
set_env DB_SSLMODE "$DB_SSLMODE"
set_env SESSION_SECURE_COOKIE false

export DEBIAN_FRONTEND=noninteractive
log 'Installing Nginx, PHP-FPM and PostgreSQL client...'
apt-get update
apt-get install -y nginx php8.3-fpm postgresql-client

log 'Installing PHP dependencies...'
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
log 'Building frontend assets...'
npm ci --no-audit --no-fund
npm run build

log 'Generating application key and running database migrations...'
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan view:cache

id -u snck-panel >/dev/null 2>&1 || useradd --system --home-dir "$APP_DIR" --shell /usr/sbin/nologin snck-panel
chown -R snck-panel:snck-panel "$APP_DIR"
install -d -o snck-panel -g snck-panel -m 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} +
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} +

# Give PHP-FPM its own pool identity so it can write only to Laravel's writable
# paths without making the application world-writable.
cat >/etc/php/8.3/fpm/pool.d/snck-panel.conf <<EOF
[snck-panel]
user = snck-panel
group = snck-panel
listen = /run/php/snck-panel.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 12
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 4
php_admin_value[upload_max_filesize] = 512M
php_admin_value[post_max_size] = 520M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_time] = 300
chdir = /
EOF

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
        fastcgi_pass unix:/run/php/snck-panel.sock;
    }
    location ~ /\. { deny all; }
}
EOF
ln -sfn /etc/nginx/sites-available/snck-panel-laravel /etc/nginx/sites-enabled/snck-panel-laravel
SERVER_NAME="$(php -r '$host = parse_url($argv[1], PHP_URL_HOST); echo $host ?: "_";' "$APP_URL")"
sed -i "s/server_name _;/server_name $SERVER_NAME;/" /etc/nginx/sites-available/snck-panel-laravel
nginx -t
php-fpm8.3 -t
systemctl restart php8.3-fpm
systemctl enable --now nginx php8.3-fpm

cat >/etc/systemd/system/snck-panel-laravel.service <<EOF
[Unit]
Description=SNCK Laravel Panel queue worker
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=snck-panel
Group=snck-panel
WorkingDirectory=$APP_DIR
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

cat >/etc/systemd/system/snck-panel-discord-worker.service <<EOF
[Unit]
Description=SNCK Discord member reconciliation and retention worker
After=network-online.target
Wants=network-online.target

[Service]
Type=oneshot
User=snck-panel
Group=snck-panel
WorkingDirectory=$APP_DIR
EnvironmentFile=$APP_DIR/.env
ExecStart=$(command -v php) $APP_DIR/artisan discord:maintenance --reconcile --cleanup
EOF

cat >/etc/systemd/system/snck-panel-discord-worker.timer <<EOF
[Unit]
Description=Run SNCK Discord maintenance every five minutes

[Timer]
OnBootSec=5min
OnUnitActiveSec=5min
Unit=snck-panel-discord-worker.service

[Install]
WantedBy=timers.target
EOF

systemctl daemon-reload
systemctl enable --now snck-panel-laravel.service snck-panel-laravel-scheduler.timer snck-panel-discord-worker.timer

log 'Laravel panel installation completed.'
printf '\nPanel directory: %s\n' "$APP_DIR"
printf 'First-owner setup: %s/setup\n' "$APP_URL"
printf 'HTTP listener: port 80 (configure HTTPS before public use)\n'
printf 'Status: systemctl status nginx php8.3-fpm snck-panel-laravel --no-pager\n'
printf 'Logs: journalctl -u snck-panel-laravel -f\n'
\n'* && "$value" != *
set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "$APP_URL"
set_env DB_CONNECTION pgsql
set_env DB_HOST "$DB_HOST"
set_env DB_PORT "$DB_PORT"
set_env DB_DATABASE "$DB_DATABASE"
set_env DB_USERNAME "$DB_USERNAME"
set_env DB_PASSWORD "$DB_PASSWORD"
set_env DB_SSLMODE "$DB_SSLMODE"
set_env SESSION_SECURE_COOKIE false

export DEBIAN_FRONTEND=noninteractive
log 'Installing Nginx, PHP-FPM and PostgreSQL client...'
apt-get update
apt-get install -y nginx php8.3-fpm postgresql-client

log 'Installing PHP dependencies...'
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
log 'Building frontend assets...'
npm ci --no-audit --no-fund
npm run build

log 'Generating application key and running database migrations...'
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan view:cache

id -u snck-panel >/dev/null 2>&1 || useradd --system --home-dir "$APP_DIR" --shell /usr/sbin/nologin snck-panel
chown -R snck-panel:snck-panel "$APP_DIR"
install -d -o snck-panel -g snck-panel -m 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} +
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} +

# Give PHP-FPM its own pool identity so it can write only to Laravel's writable
# paths without making the application world-writable.
cat >/etc/php/8.3/fpm/pool.d/snck-panel.conf <<EOF
[snck-panel]
user = snck-panel
group = snck-panel
listen = /run/php/snck-panel.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 12
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 4
clear_env = no
chdir = /
EOF

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
        fastcgi_pass unix:/run/php/snck-panel.sock;
    }
    location ~ /\. { deny all; }
}
EOF
ln -sfn /etc/nginx/sites-available/snck-panel-laravel /etc/nginx/sites-enabled/snck-panel-laravel
SERVER_NAME="$(php -r '$host = parse_url($argv[1], PHP_URL_HOST); echo $host ?: "_";' "$APP_URL")"
sed -i "s/server_name _;/server_name $SERVER_NAME;/" /etc/nginx/sites-available/snck-panel-laravel
nginx -t
php-fpm8.3 -t
systemctl restart php8.3-fpm
systemctl enable --now nginx php8.3-fpm

cat >/etc/systemd/system/snck-panel-laravel.service <<EOF
[Unit]
Description=SNCK Laravel Panel queue worker
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=snck-panel
Group=snck-panel
WorkingDirectory=$APP_DIR
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

cat >/etc/systemd/system/snck-panel-discord-worker.service <<EOF
[Unit]
Description=SNCK Discord member reconciliation and retention worker
After=network-online.target
Wants=network-online.target

[Service]
Type=oneshot
User=snck-panel
Group=snck-panel
WorkingDirectory=$APP_DIR
EnvironmentFile=$APP_DIR/.env
ExecStart=$(command -v php) $APP_DIR/artisan discord:maintenance --reconcile --cleanup
EOF

cat >/etc/systemd/system/snck-panel-discord-worker.timer <<EOF
[Unit]
Description=Run SNCK Discord maintenance every five minutes

[Timer]
OnBootSec=5min
OnUnitActiveSec=5min
Unit=snck-panel-discord-worker.service

[Install]
WantedBy=timers.target
EOF

systemctl daemon-reload
systemctl enable --now snck-panel-laravel.service snck-panel-laravel-scheduler.timer snck-panel-discord-worker.timer

log 'Laravel panel installation completed.'
printf '\nPanel directory: %s\n' "$APP_DIR"
printf 'First-owner setup: %s/setup\n' "$APP_URL"
printf 'HTTP listener: port 80 (configure HTTPS before public use)\n'
printf 'Status: systemctl status nginx php8.3-fpm snck-panel-laravel --no-pager\n'
printf 'Logs: journalctl -u snck-panel-laravel -f\n'
\r'* ]] || fail "$key cannot contain a newline."
  SNCK_ENV_KEY="$key" SNCK_ENV_VALUE="$value" php -r '
    $path = ".env";
    $key = getenv("SNCK_ENV_KEY");
    $value = getenv("SNCK_ENV_VALUE");
    $quoted = "\"" . str_replace(["\\\\", "\""], ["\\\\\\\\", "\\\\\""], $value) . "\"";
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) { fwrite(STDERR, "Cannot read .env\\n"); exit(1); }
    $prefix = $key . "=";
    $updated = false;
    foreach ($lines as &$line) {
      if (str_starts_with($line, $prefix)) {
        $line = $prefix . $quoted;
        $updated = true;
      }
    }
    unset($line);
    if (!$updated) { $lines[] = $prefix . $quoted; }
    if (file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX) === false) {
      fwrite(STDERR, "Cannot write .env\\n"); exit(1);
    }
  ' || fail "Could not update $key in .env."
}
set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "$APP_URL"
set_env DB_CONNECTION pgsql
set_env DB_HOST "$DB_HOST"
set_env DB_PORT "$DB_PORT"
set_env DB_DATABASE "$DB_DATABASE"
set_env DB_USERNAME "$DB_USERNAME"
set_env DB_PASSWORD "$DB_PASSWORD"
set_env DB_SSLMODE "$DB_SSLMODE"
set_env SESSION_SECURE_COOKIE false

export DEBIAN_FRONTEND=noninteractive
log 'Installing Nginx, PHP-FPM and PostgreSQL client...'
apt-get update
apt-get install -y nginx php8.3-fpm postgresql-client

log 'Installing PHP dependencies...'
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
log 'Building frontend assets...'
npm ci --no-audit --no-fund
npm run build

log 'Generating application key and running database migrations...'
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan view:cache

id -u snck-panel >/dev/null 2>&1 || useradd --system --home-dir "$APP_DIR" --shell /usr/sbin/nologin snck-panel
chown -R snck-panel:snck-panel "$APP_DIR"
install -d -o snck-panel -g snck-panel -m 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} +
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} +

# Give PHP-FPM its own pool identity so it can write only to Laravel's writable
# paths without making the application world-writable.
cat >/etc/php/8.3/fpm/pool.d/snck-panel.conf <<EOF
[snck-panel]
user = snck-panel
group = snck-panel
listen = /run/php/snck-panel.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 12
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 4
clear_env = no
chdir = /
EOF

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
        fastcgi_pass unix:/run/php/snck-panel.sock;
    }
    location ~ /\. { deny all; }
}
EOF
ln -sfn /etc/nginx/sites-available/snck-panel-laravel /etc/nginx/sites-enabled/snck-panel-laravel
SERVER_NAME="$(php -r '$host = parse_url($argv[1], PHP_URL_HOST); echo $host ?: "_";' "$APP_URL")"
sed -i "s/server_name _;/server_name $SERVER_NAME;/" /etc/nginx/sites-available/snck-panel-laravel
nginx -t
php-fpm8.3 -t
systemctl restart php8.3-fpm
systemctl enable --now nginx php8.3-fpm

cat >/etc/systemd/system/snck-panel-laravel.service <<EOF
[Unit]
Description=SNCK Laravel Panel queue worker
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=snck-panel
Group=snck-panel
WorkingDirectory=$APP_DIR
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

cat >/etc/systemd/system/snck-panel-discord-worker.service <<EOF
[Unit]
Description=SNCK Discord member reconciliation and retention worker
After=network-online.target
Wants=network-online.target

[Service]
Type=oneshot
User=snck-panel
Group=snck-panel
WorkingDirectory=$APP_DIR
EnvironmentFile=$APP_DIR/.env
ExecStart=$(command -v php) $APP_DIR/artisan discord:maintenance --reconcile --cleanup
EOF

cat >/etc/systemd/system/snck-panel-discord-worker.timer <<EOF
[Unit]
Description=Run SNCK Discord maintenance every five minutes

[Timer]
OnBootSec=5min
OnUnitActiveSec=5min
Unit=snck-panel-discord-worker.service

[Install]
WantedBy=timers.target
EOF

systemctl daemon-reload
systemctl enable --now snck-panel-laravel.service snck-panel-laravel-scheduler.timer snck-panel-discord-worker.timer

log 'Laravel panel installation completed.'
printf '\nPanel directory: %s\n' "$APP_DIR"
printf 'First-owner setup: %s/setup\n' "$APP_URL"
printf 'HTTP listener: port 80 (configure HTTPS before public use)\n'
printf 'Status: systemctl status nginx php8.3-fpm snck-panel-laravel --no-pager\n'
printf 'Logs: journalctl -u snck-panel-laravel -f\n'
