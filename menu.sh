#!/usr/bin/env bash
set -Eeuo pipefail

REPO="https://raw.githubusercontent.com/SnckBoy/snck-panel"
BRANCH="feat/snck-control-center-v2"
APP_DIR="${SNCK_PANEL_DIR:-/var/www/snck-panel}"
BACKUP_ROOT="/root/snck-panel-backups"

cyan=$'\033[1;36m'; green=$'\033[1;32m'; red=$'\033[1;31m'; reset=$'\033[0m'
log() { printf '%s[SNCK]%s %s\n' "$cyan" "$reset" "$*"; }
ok() { printf '%s[ OK ]%s %s\n' "$green" "$reset" "$*"; }
fail() { printf '%s[ERROR]%s %s\n' "$red" "$reset" "$*" >&2; exit 1; }
need_root() { [[ "$EUID" -eq 0 ]] || fail "Run with sudo or as root."; }
pause() { read -r -p "Press Enter to return to the menu..." _ </dev/tty; }
app_exists() { [[ -f "$APP_DIR/artisan" && -f "$APP_DIR/.env" ]]; }

install_local_node_helper() {
    if ! command -v sudo >/dev/null 2>&1; then apt-get update && apt-get install -y sudo; fi
    [[ -f "$APP_DIR/scripts/snck-local-node-bootstrap.sh" ]] || fail "Local node bootstrap helper is missing from this branch."
    install -o root -g root -m 0755 "$APP_DIR/scripts/snck-local-node-bootstrap.sh" /usr/local/sbin/snck-local-node-bootstrap
    printf '%s\n' 'www-data ALL=(root) NOPASSWD: /usr/local/sbin/snck-local-node-bootstrap ""' >/etc/sudoers.d/snck-local-node
    chmod 0440 /etc/sudoers.d/snck-local-node
    visudo -cf /etc/sudoers.d/snck-local-node >/dev/null || fail "Local node sudoers rule failed validation."
}

backup_app() {
    app_exists || fail "No existing SNCK Panel installation at $APP_DIR."
    local stamp db_host db_port db_name db_user db_pass
    stamp="$(date +%Y%m%d-%H%M%S)"
    mkdir -p "$BACKUP_ROOT/$stamp"
    chmod 700 "$BACKUP_ROOT" "$BACKUP_ROOT/$stamp"
    cp -a "$APP_DIR/.env" "$BACKUP_ROOT/$stamp/.env"
    if [[ -d "$APP_DIR/.git" && -f "$APP_DIR/vendor/autoload.php" ]]; then
        readarray -t db_values < <(cd "$APP_DIR" && php -r '
            require "vendor/autoload.php";
            $env = Dotenv\Dotenv::createImmutable(getcwd())->safeLoad();
            foreach (["DB_HOST","DB_PORT","DB_DATABASE","DB_USERNAME","DB_PASSWORD"] as $key) {
                echo ($env[$key] ?? "") . PHP_EOL;
            }
        ')
        db_host="${db_values[0]:-127.0.0.1}"
        db_port="${db_values[1]:-3306}"
        db_name="${db_values[2]:-}"
        db_user="${db_values[3]:-}"
        db_pass="${db_values[4]:-}"
        [[ -n "$db_name" && -n "$db_user" ]] || fail "Could not read database settings safely from .env."
        MYSQL_PWD="$db_pass" mysqldump --single-transaction --quick --host="$db_host" --port="$db_port" --user="$db_user" "$db_name" > "$BACKUP_ROOT/$stamp/database.sql"
        unset db_pass
    else
        fail "Panel dependencies are missing; refusing to create an incomplete backup."
    fi
    chmod 600 "$BACKUP_ROOT/$stamp/"*
    printf '%s\n' "$BACKUP_ROOT/$stamp"
}

install_panel() {
    [[ ! -e "$APP_DIR/.env" ]] || fail "An installation already exists at $APP_DIR. Use Update or Repair."
    log "Starting the supported fresh-install workflow..."
    curl -fsSL "$REPO/$BRANCH/install.sh" | env SNCK_BRANCH="$BRANCH" SNCK_PANEL_DIR="$APP_DIR" bash
}

update_panel() {
    app_exists || fail "No existing installation found."
    [[ -d "$APP_DIR/.git" ]] || fail "This installation has no Git metadata; automatic update is unsafe."
    git -c safe.directory="$APP_DIR" -C "$APP_DIR" diff --quiet || fail "Uncommitted tracked changes detected. Commit or back them up before updating."
    [[ -z "$(git -c safe.directory="$APP_DIR" -C "$APP_DIR" status --porcelain)" ]] || fail "Working tree is not clean. Resolve local changes before updating."
    local backup
    backup="$(backup_app)"
    log "Backup saved to $backup"
    local was_down=0
    trap 'if [[ "$was_down" == "1" ]]; then (cd "$APP_DIR" && php artisan up >/dev/null 2>&1 || true); fi' ERR
    cd "$APP_DIR"
    php artisan down || true
    was_down=1
    git -c safe.directory="$APP_DIR" fetch --prune origin "+refs/heads/$BRANCH:refs/remotes/origin/$BRANCH"
    git -c safe.directory="$APP_DIR" checkout -B "$BRANCH" "origin/$BRANCH"
    install_local_node_helper
    COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
    if [[ ! -f /opt/snck-yarn/node_modules/yarn/bin/yarn.js ]]; then
        npm install --prefix /opt/snck-yarn --no-save --ignore-scripts --no-audit --no-fund yarn@1.22.22
    fi
    local yarn_js="/opt/snck-yarn/node_modules/yarn/bin/yarn.js"
    node "$yarn_js" install --frozen-lockfile
    node "$yarn_js" tsc
    node "$yarn_js" lint
    node "$yarn_js" build:production
    php artisan migrate --force --no-interaction
    php artisan optimize:clear
    php artisan config:cache
    php artisan view:cache
    chown -R www-data:www-data "$APP_DIR"
    find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 0755 {} +
    chmod 640 "$APP_DIR/.env"
    nginx -t
    systemctl restart snck-panel-queue php8.3-fpm nginx cron
    php artisan up
    was_down=0
    trap - ERR
    ok "Panel updated. Database and .env backup: $backup"
}

repair_panel() {
    app_exists || fail "No existing installation found."
    cd "$APP_DIR"
    install_local_node_helper
    php artisan optimize:clear
    php artisan config:cache
    php artisan view:cache
    chown -R www-data:www-data "$APP_DIR"
    find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 0755 {} +
    chmod 640 "$APP_DIR/.env"
    nginx -t
    systemctl daemon-reload
    systemctl enable --now mariadb redis-server php8.3-fpm nginx cron snck-panel-queue
    systemctl restart php8.3-fpm nginx snck-panel-queue cron
    ok "Cache, permissions and panel services repaired."
}

diagnostics() {
    echo
    echo "===== SYSTEM ====="
    . /etc/os-release; printf '%s %s | %s\n' "$PRETTY_NAME" "$(uname -m)" "$(uname -r)"
    echo "===== PANEL ====="
    if app_exists; then
        cd "$APP_DIR"
        php artisan --version
        grep '^APP_URL=' .env || true
        printf 'Git branch: '; git -c safe.directory="$APP_DIR" -C "$APP_DIR" branch --show-current 2>/dev/null || true
        printf 'Git commit: '; git -c safe.directory="$APP_DIR" -C "$APP_DIR" rev-parse --short HEAD 2>/dev/null || true
        printf 'HTTP: '; APP_URL_VALUE="$(php -r 'require "vendor/autoload.php"; $e=Dotenv\Dotenv::createImmutable(getcwd())->safeLoad(); echo $e["APP_URL"] ?? "";')"
        if [[ -n "$APP_URL_VALUE" ]]; then curl -kLsS -o /dev/null -w '%{http_code}\n' --max-time 10 "$APP_URL_VALUE" || true; else echo "APP_URL unavailable"; fi
    else
        echo "Not installed at $APP_DIR"
    fi
    echo "===== SERVICES ====="
    for service in mariadb redis-server php8.3-fpm nginx cron snck-panel-queue wings; do
        printf '%-20s' "$service"; systemctl is-active "$service" 2>/dev/null || true
    done
    echo "===== NGINX ====="
    nginx -t 2>&1 || true
    echo "===== DISK ====="
    df -h / | tail -n 1
}

install_wings() {
    local panel_url node_id
    read -r -p "Panel HTTPS URL (required): " panel_url </dev/tty
    read -r -p "Node ID from Admin > Nodes: " node_id </dev/tty
    [[ "$panel_url" == https://* ]] || fail "Wings auto-configuration requires an HTTPS Panel URL."
    [[ "$node_id" =~ ^[0-9]+$ ]] || fail "Node ID must be numeric."
    curl -fsSL "$REPO/$BRANCH/node-install.sh" | env SNCK_PANEL_URL="$panel_url" SNCK_NODE_ID="$node_id" bash
}

uninstall_panel() {
    app_exists || fail "No existing installation found."
    echo "This will disable the Panel and move its files into a timestamped recovery folder."
    echo "The database, Docker data and Wings nodes will NOT be deleted."
    read -r -p "Type DISABLE to continue: " confirm </dev/tty
    [[ "$confirm" == "DISABLE" ]] || { log "Cancelled."; return; }
    local target="${APP_DIR}.disabled.$(date +%Y%m%d-%H%M%S)"
    systemctl disable --now snck-panel-queue 2>/dev/null || true
    rm -f /etc/nginx/sites-enabled/snck-panel.conf
    nginx -t && systemctl reload nginx
    mv "$APP_DIR" "$target"
    ok "Panel files moved to $target. Database and node data were retained."
}

show_menu() {
    clear 2>/dev/null || true
    printf '%s\n' "╔════════════════════════════════════════════╗"
    printf '%s\n' "║          SNCK CONTROL CENTER               ║"
    printf '%s\n' "║       Panel Installer & Manager            ║"
    printf '%s\n' "╚════════════════════════════════════════════╝"
    echo "  1) Fresh Panel installation"
    echo "  2) Update Panel (backup + build + migrate)"
    echo "  3) Repair Panel (cache + permissions + services)"
    echo "  4) Diagnostics"
    echo "  5) Backup database and environment"
    echo "  6) Install Wings on this node (HTTPS required)"
    echo "  7) Restart Panel services"
    echo "  8) Disable Panel (reversible; keeps database)"
    echo "  0) Exit"
    echo
}

usage() {
    cat <<'EOF'
SNCK Control Center manager

Usage:
  curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-control-center-v2/menu.sh | sudo bash

Options:
  --help       Show this help
  (no option)  Open the interactive manager menu

Menu actions include fresh install, update, repair, diagnostics, backup,
Wings installation, service restart, and reversible panel disable.
EOF
}

if [[ "${1:-}" == "--help" || "${1:-}" == "-h" ]]; then
    usage
    exit 0
fi

need_root
[[ -r /dev/tty ]] || fail "Run from an interactive terminal; menu input requires /dev/tty."
while true; do
    show_menu
    read -r -p "Select an option: " choice </dev/tty
    case "$choice" in
        1) install_panel; pause ;;
        2) update_panel; pause ;;
        3) repair_panel; pause ;;
        4) diagnostics; pause ;;
        5) path="$(backup_app)"; ok "Backup saved to $path"; pause ;;
        6) install_wings; pause ;;
        7) systemctl restart php8.3-fpm nginx snck-panel-queue cron; ok "Services restarted."; pause ;;
        8) uninstall_panel; pause ;;
        0) exit 0 ;;
        *) echo "Invalid option."; pause ;;
    esac
done
