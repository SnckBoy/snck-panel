#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="${SNCK_PANEL_DIR:-/var/www/snck-panel}"
BRANCH="${SNCK_BRANCH:-feat/snck-control-center-v2}"
REPOSITORY="${SNCK_REPOSITORY:-https://github.com/SnckBoy/snck-panel.git}"
APP_URL="${SNCK_APP_URL:-}"
LE_EMAIL="${SNCK_LETSENCRYPT_EMAIL:-}"
DB_NAME="${SNCK_DB_NAME:-snck_panel}"
DB_USER="${SNCK_DB_USER:-snck_panel}"
DB_PASSWORD="${SNCK_DB_PASSWORD:-}"
SERVICE_AUTHOR="${SNCK_SERVICE_AUTHOR:-}"

log() { printf '\033[1;36m[SNCK]\033[0m %s\n' "$*"; }
ok() { printf '\033[1;32m[ OK ]\033[0m %s\n' "$*"; }
fail() { printf '\033[1;31m[ERROR]\033[0m %s\n' "$*" >&2; exit 1; }

usage() {
  cat <<'EOF'
SNCK Panel installer (Pterodactyl v1.15.2 based)

Usage:
  curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-control-center-v2/install.sh | sudo bash

Optional environment:
  SNCK_BRANCH                 Git branch (default: feat/snck-control-center-v2)
  SNCK_PANEL_DIR              Install directory (default: /var/www/snck-panel)
  SNCK_APP_URL                Panel URL, e.g. https://panel.example.com
  SNCK_LETSENCRYPT_EMAIL      Required for automatic HTTPS
  SNCK_DB_NAME                Database name (default: snck_panel)
  SNCK_DB_USER                Database user (default: snck_panel)
  SNCK_DB_PASSWORD            Database password (generated if omitted)
  SNCK_SERVICE_AUTHOR         Email used as the default Egg author

The installer requires an interactive terminal to create the first administrator.
It installs the Panel only. Install SNCK Wings separately on every game node.
EOF
}

if [[ "${1:-}" == "--help" || "${1:-}" == "-h" ]]; then usage; exit 0; fi
[[ "$EUID" -eq 0 ]] || fail "Run this installer as root (sudo)."
[[ -r /dev/tty ]] || fail "An interactive terminal is required for the first administrator setup."
command -v openssl >/dev/null 2>&1 || fail "OpenSSL is required to generate secure database credentials."

[[ -r /etc/os-release ]] || fail "Cannot identify this operating system."
. /etc/os-release
case "${ID:-}" in
  ubuntu)
    [[ "${VERSION_ID:-}" == "22.04" || "${VERSION_ID:-}" == "24.04" ]] || fail "Supported Ubuntu versions: 22.04 and 24.04."
    ;;
  debian)
    [[ "${VERSION_ID:-}" == "11" || "${VERSION_ID:-}" == "12" || "${VERSION_ID:-}" == "13" ]] || fail "Supported Debian versions: 11, 12 and 13."
    ;;
  *) fail "Supported operating systems: Ubuntu 22.04/24.04 and Debian 11/12/13." ;;
esac

if [[ -z "$APP_URL" ]]; then
  read -r -p "Panel URL (include http:// or https://): " APP_URL </dev/tty
fi
[[ "$APP_URL" =~ ^https?://[^/[:space:]]+/?$ ]] || fail "SNCK_APP_URL must be a root HTTP/HTTPS URL without a path."
APP_URL="${APP_URL%/}"
APP_AUTHORITY="${APP_URL#*://}"
APP_AUTHORITY="${APP_AUTHORITY%/}"
APP_HOST="${APP_AUTHORITY%%:*}"
if [[ "$APP_AUTHORITY" == *:* ]]; then
  APP_PORT="${APP_AUTHORITY##*:}"
  case "$APP_URL" in
    http://*) [[ "$APP_PORT" == "80" ]] || fail "HTTP installs must use port 80; configure Nginx for a standard public web port." ;;
    https://*) [[ "$APP_PORT" == "443" ]] || fail "HTTPS installs must use port 443; configure Nginx for a standard public web port." ;;
  esac
fi
[[ "$APP_HOST" =~ ^([A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?)(\.([A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?))*$ && ${#APP_HOST} -le 253 ]] || fail "SNCK_APP_URL must contain a valid DNS hostname or IPv4 address."
if command -v ss >/dev/null 2>&1; then
  if ss -ltnH '( sport = :80 )' | grep -q . && ! systemctl is-active --quiet nginx; then
    fail "TCP port 80 is already occupied by a non-Nginx service. Stop it or use a fresh VPS before installing SNCK."
  fi
  if [[ "$APP_URL" == https://* ]] && ss -ltnH '( sport = :443 )' | grep -q . && ! systemctl is-active --quiet nginx; then
    fail "TCP port 443 is already occupied by a non-Nginx service. Stop it before enabling HTTPS."
  fi
fi
if [[ "$APP_URL" == https://* ]]; then
  [[ ! "$APP_HOST" =~ ^([0-9]{1,3}\.){3}[0-9]{1,3}$ ]] || fail "HTTPS requires a DNS hostname, not a raw IP address."
  [[ -n "$LE_EMAIL" ]] || read -r -p "Let's Encrypt email: " LE_EMAIL </dev/tty
  [[ "$LE_EMAIL" =~ ^[^@[:space:]]+@[^@[:space:]]+\.[^@[:space:]]+$ ]] || fail "A valid SNCK_LETSENCRYPT_EMAIL is required for HTTPS."
fi

[[ ! -e "$APP_DIR/.env" ]] || fail "An existing .env was found in $APP_DIR. Refusing to overwrite an existing installation."
if [[ -d "$APP_DIR" && -n "$(find "$APP_DIR" -mindepth 1 -maxdepth 1 -print -quit)" ]]; then
  fail "The install directory $APP_DIR is not empty. Choose a fresh SNCK_PANEL_DIR."
fi
[[ "$DB_NAME" =~ ^[A-Za-z0-9_]+$ ]] || fail "Invalid database name."
[[ "$DB_USER" =~ ^[A-Za-z0-9_]+$ ]] || fail "Invalid database username."
[[ -n "$DB_PASSWORD" ]] || DB_PASSWORD="$(openssl rand -hex 32)"
[[ "$DB_PASSWORD" =~ ^[A-Za-z0-9]+$ ]] || fail "SNCK_DB_PASSWORD must contain only letters and digits."
[[ -n "$SERVICE_AUTHOR" ]] || SERVICE_AUTHOR="admin@snckpanel.invalid"
[[ "$SERVICE_AUTHOR" =~ ^[^@[:space:]]+@[^@[:space:]]+\.[^@[:space:]]+$ ]] || fail "SNCK_SERVICE_AUTHOR must be a valid email address."

export DEBIAN_FRONTEND=noninteractive
log "Installing system packages for $PRETTY_NAME..."
apt-get update
apt-get install -y ca-certificates curl gnupg lsb-release software-properties-common apt-transport-https git unzip tar rsync openssl mariadb-server redis-server nginx cron certbot python3-certbot-nginx sudo sudo

if [[ "$ID" == "debian" ]]; then
  KEYRING_PACKAGE="$(mktemp /tmp/debsuryorg-keyring.XXXXXX.deb)"
  curl -fsSLo "$KEYRING_PACKAGE" https://packages.sury.org/debsuryorg-archive-keyring.deb
  dpkg -i "$KEYRING_PACKAGE"
  rm -f "$KEYRING_PACKAGE"
  cat >/etc/apt/sources.list.d/php-sury.sources <<EOF
Types: deb
URIs: https://packages.sury.org/php/
Suites: $VERSION_CODENAME
Components: main
Signed-By: /usr/share/keyrings/debsuryorg-archive-keyring.gpg
EOF
elif [[ "$VERSION_ID" == "22.04" ]]; then
  add-apt-repository -y ppa:ondrej/php
fi

curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt-get update
apt-get install -y php8.3-cli php8.3-common php8.3-gd php8.3-mysql php8.3-mbstring php8.3-bcmath php8.3-xml php8.3-fpm php8.3-curl php8.3-zip php8.3-intl php8.3-opcache nodejs
update-alternatives --set php /usr/bin/php8.3

if ! command -v composer >/dev/null 2>&1; then
  EXPECTED_CHECKSUM="$(curl -fsSL https://composer.github.io/installer.sig)"
  INSTALLER="$(mktemp /tmp/composer-setup.XXXXXX.php)"
  curl -fsSLo "$INSTALLER" https://getcomposer.org/installer
  ACTUAL_CHECKSUM="$(php -r 'echo hash_file("sha384", $argv[1]);' "$INSTALLER")"
  [[ "$EXPECTED_CHECKSUM" == "$ACTUAL_CHECKSUM" ]] || fail "Composer installer checksum verification failed."
  php "$INSTALLER" --install-dir=/usr/local/bin --filename=composer
  rm -f "$INSTALLER"
fi
# Install Yarn Classic into a fixed location and invoke its JS entry point.
# This avoids npm 11's global-bin/PATH and install-script policy differences.
YARN_PREFIX="/opt/snck-yarn"
YARN_JS="$YARN_PREFIX/node_modules/yarn/bin/yarn.js"
if [[ ! -f "$YARN_JS" ]]; then
  log "Installing Yarn Classic 1.22.22..."
  npm install --prefix "$YARN_PREFIX" --no-save --ignore-scripts --no-audit --no-fund yarn@1.22.22
fi
[[ -f "$YARN_JS" ]] || fail "Yarn installation did not create $YARN_JS."
YARN=(node "$YARN_JS")
"${YARN[@]}" --version | grep -qx "1.22.22" || fail "Yarn Classic 1.22.22 could not be started."

systemctl enable --now mariadb redis-server nginx php8.3-fpm cron
log "Creating the SNCK database and restricted database user..."
DB_EXISTS="$(mysql --protocol=socket -uroot -Nse "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name = '$DB_NAME'")"
USER_EXISTS="$(mysql --protocol=socket -uroot -Nse "SELECT COUNT(*) FROM mysql.user WHERE User = '$DB_USER' AND Host = '127.0.0.1'")"
[[ "$DB_EXISTS" == "0" && "$USER_EXISTS" == "0" ]] || fail "The requested database or database user already exists. Choose unique SNCK_DB_NAME and SNCK_DB_USER values."
mysql --protocol=socket -uroot <<SQL
CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

log "Downloading SNCK Panel branch $BRANCH..."
mkdir -p "$(dirname "$APP_DIR")"
git clone --depth 1 --branch "$BRANCH" "$REPOSITORY" "$APP_DIR"
cd "$APP_DIR"
[[ -f LICENSE.md && -f SNCK_UPSTREAM.md && -f artisan ]] || fail "The selected branch is not a complete SNCK Pterodactyl fork."

log "Installing the restricted local Wings bootstrap helper..."
[[ -f scripts/snck-local-node-bootstrap.sh ]] || fail "Local node bootstrap helper is missing from this branch."
install -o root -g root -m 0755 scripts/snck-local-node-bootstrap.sh /usr/local/sbin/snck-local-node-bootstrap
printf '%s\n' 'www-data ALL=(root) NOPASSWD: /usr/local/sbin/snck-local-node-bootstrap ""' >/etc/sudoers.d/snck-local-node
chmod 0440 /etc/sudoers.d/snck-local-node
visudo -cf /etc/sudoers.d/snck-local-node >/dev/null || fail "Local node sudoers rule failed validation."

cp .env.example .env
APP_KEY_VALUE="$(openssl rand -base64 32)"
sed -i "s|^APP_KEY=.*|APP_KEY=base64:${APP_KEY_VALUE}|" .env
install -d -o www-data -g www-data -m 0775 \
  storage/app/private storage/app/public \
  storage/framework/cache/data storage/framework/sessions \
  storage/framework/views storage/logs
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
composer check-platform-reqs --no-dev
php artisan p:environment:setup --author="$SERVICE_AUTHOR" --url="$APP_URL" --timezone=UTC --cache=redis --session=redis --queue=redis --redis-host=127.0.0.1 --redis-pass= --redis-port=6379 --settings-ui=true --telemetry=0 --no-interaction
php artisan p:environment:database --host=127.0.0.1 --port=3306 --database="$DB_NAME" --username="$DB_USER" --password="$DB_PASSWORD" --no-interaction

log "Building the frontend assets (this may take several minutes)..."
"${YARN[@]}" install --frozen-lockfile
"${YARN[@]}" tsc
"${YARN[@]}" lint
"${YARN[@]}" build:production

log "Running database migrations and importing default game server templates..."
php artisan migrate --seed --force --no-interaction

printf '\nCreate the first SNCK Panel administrator. Use a unique password.\n'
read -r -p "Administrator email: " ADMIN_EMAIL </dev/tty
read -r -p "Administrator username: " ADMIN_USERNAME </dev/tty
read -r -p "First name: " ADMIN_FIRST </dev/tty
read -r -p "Last name: " ADMIN_LAST </dev/tty
php artisan p:user:make --email="$ADMIN_EMAIL" --username="$ADMIN_USERNAME" --name-first="$ADMIN_FIRST" --name-last="$ADMIN_LAST" --admin=1 </dev/tty

chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 0755 {} +
chmod 0640 "$APP_DIR/.env"

cat >/etc/nginx/sites-available/snck-panel.conf <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name $APP_HOST;
    root $APP_DIR/public;
    index index.php;
    client_max_body_size 100m;
    charset utf-8;

    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt { access_log off; log_not_found off; }
    access_log off;
    error_log /var/log/nginx/snck-panel-error.log error;

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTP_PROXY "";
        fastcgi_intercept_errors off;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
    }

    location ~ /\. { deny all; }
}
NGINX
ln -sfn /etc/nginx/sites-available/snck-panel.conf /etc/nginx/sites-enabled/snck-panel.conf

cat >/etc/systemd/system/snck-panel-queue.service <<SERVICE
[Unit]
Description=SNCK Panel Queue Worker
After=redis-server.service mariadb.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php $APP_DIR/artisan queue:work --queue=high,standard,low --sleep=3 --tries=3
StartLimitIntervalSec=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
SERVICE

cat >/etc/cron.d/snck-panel <<CRON
* * * * * www-data cd $APP_DIR && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
CRON
chmod 0644 /etc/cron.d/snck-panel

if [[ "$APP_URL" == https://* ]]; then
  log "Requesting Let's Encrypt certificate for $APP_HOST..."
  nginx -t
  systemctl reload nginx
  certbot --nginx --non-interactive --agree-tos --redirect --email "$LE_EMAIL" -d "$APP_HOST"
fi

cd "$APP_DIR"
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
nginx -t
systemctl daemon-reload
systemctl enable --now snck-panel-queue.service
systemctl restart php8.3-fpm nginx

install -d -m 0700 /root/snck-panel-secrets
cp .env /root/snck-panel-secrets/.env.backup
chmod 0600 /root/snck-panel-secrets/.env.backup
ok "SNCK Panel installed at $APP_DIR"
printf '\nPanel URL: %s\nAdmin email: %s\nEnvironment backup: /root/snck-panel-secrets/.env.backup\n' "$APP_URL" "$ADMIN_EMAIL"
printf 'Next: install SNCK Wings on each game node and configure it from Admin > Nodes.\n'
