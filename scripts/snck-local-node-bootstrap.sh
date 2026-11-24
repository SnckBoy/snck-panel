#!/usr/bin/env bash
set -Eeuo pipefail

WINGS_VERSION="v1.13.5"
CONFIG_ROOT="/var/lib/snck-local-node"
HELPER="/usr/local/sbin/snck-local-node-bootstrap"

log() { printf '[SNCK-LOCAL-NODE] %s\n' "$*"; }
fail() { printf '[SNCK-LOCAL-NODE] ERROR: %s\n' "$*" >&2; exit 1; }

if [[ "${1:-}" == "--run" ]]; then
    [[ "$EUID" -eq 0 ]] || fail "Background installer must run as root."
    NODE_ID="${2:-}"
    NODE_HOST="${3:-}"
    [[ "$NODE_ID" =~ ^[0-9]+$ ]] || fail "Invalid node ID."
    [[ "$NODE_HOST" =~ ^[a-zA-Z0-9][a-zA-Z0-9.:_-]*[a-zA-Z0-9]$ ]] || fail "Invalid node hostname."
    CONFIG_FILE="$CONFIG_ROOT/config-$NODE_ID.yml"
    LOG_FILE="/var/log/snck-local-node-$NODE_ID.log"
    exec >>"$LOG_FILE" 2>&1
    trap 'code=$?; printf "[%s] Setup failed (exit %s).\n" "$(date -Is)" "$code"; exit "$code"' ERR

    [[ -s "$CONFIG_FILE" ]] || fail "Node configuration file is missing."
    if ss -ltnH | grep -Eq ":(8080|2022)[[:space:]]"; then fail "TCP port 8080 or 2022 is already in use."; fi
    [[ ! -e /etc/pterodactyl/config.yml ]] || fail "An existing Wings configuration was found. Refusing to overwrite it."
    [[ -r /etc/os-release ]] || fail "Cannot identify operating system."
    . /etc/os-release
    case "${ID:-}:${VERSION_ID:-}" in
        ubuntu:20.04|ubuntu:22.04|ubuntu:24.04|debian:11|debian:12|debian:13) ;;
        *) fail "Supported systems: Ubuntu 20.04/22.04/24.04 and Debian 11/12/13." ;;
    esac

    export DEBIAN_FRONTEND=noninteractive
    if ! command -v docker >/dev/null 2>&1; then
        log "Installing Docker Engine..."
        apt-get update
        apt-get install -y ca-certificates curl gnupg docker.io
    fi
    systemctl enable --now docker

    if [[ ! -x /usr/local/bin/wings ]]; then
        case "$(uname -m)" in
            x86_64|amd64)
                ASSET="wings_linux_amd64"
                EXPECTED_SHA256="231134aa6d5aa02aac4f6384b50c76860637858cbc056268b9f96ac42f7f5393"
                ;;
            aarch64|arm64)
                ASSET="wings_linux_arm64"
                EXPECTED_SHA256="04303d7cb19e4037902d154d4f07eb643b948429f8fdc0adbc6bf7ea93f31897"
                ;;
            *) fail "Unsupported CPU architecture: $(uname -m)." ;;
        esac
        TMP="$(mktemp /tmp/snck-wings.XXXXXX)"
        trap 'code=$?; rm -f "$TMP"; if [[ $code -ne 0 ]]; then printf "[%s] Setup failed (exit %s).\n" "$(date -Is)" "$code"; fi; exit "$code"' EXIT
        DOWNLOAD_URL="https://github.com/pterodactyl/wings/releases/download/$WINGS_VERSION/$ASSET"
        log "Downloading Wings $WINGS_VERSION..."
        curl -fsSLo "$TMP" "$DOWNLOAD_URL"
        printf '%s  %s\n' "$EXPECTED_SHA256" "$TMP" | sha256sum --check --status || fail "Wings SHA-256 verification failed."
        install -m 0755 "$TMP" /usr/local/bin/wings
        rm -f "$TMP"
    else
        log "Using the existing Wings binary; it will not be overwritten."
    fi
    install -d -m 0755 /etc/pterodactyl /var/lib/pterodactyl/volumes
    install -m 0600 "$CONFIG_FILE" /etc/pterodactyl/config.yml

    if [[ -e /etc/systemd/system/wings.service ]]; then
        cp -a /etc/systemd/system/wings.service "/etc/systemd/system/wings.service.snck-backup.$(date +%Y%m%d%H%M%S)"
    fi
    cat >/etc/systemd/system/wings.service <<'SERVICE'
[Unit]
Description=SNCK Wings Service
After=docker.service
Requires=docker.service

[Service]
User=root
WorkingDirectory=/etc/pterodactyl
LimitNOFILE=4096
ExecStart=/usr/local/bin/wings
Restart=on-failure
StartLimitIntervalSec=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
SERVICE

    systemctl daemon-reload
    systemctl enable --now wings
    sleep 3
    systemctl is-active --quiet wings || {
        journalctl -u wings -n 80 --no-pager
        fail "Wings failed to start. See $LOG_FILE and journalctl -u wings."
    }
    NODE_SCHEME="$(grep -q 'enabled: true' "$CONFIG_FILE" && printf 'https' || printf 'http')"
    NODE_TOKEN_ID="$(sed -n 's/^token_id: *//p' "$CONFIG_FILE")"
    NODE_TOKEN="$(sed -n 's/^token: *//p' "$CONFIG_FILE")"
    [[ -n "$NODE_TOKEN_ID" && -n "$NODE_TOKEN" ]] || fail "Wings configuration is missing health-check credentials."
    HEALTH_URL="${NODE_SCHEME}://${NODE_HOST}:8080/api/system"
    if [[ "$NODE_SCHEME" == "https" ]]; then
        curl --resolve "${NODE_HOST}:8080:127.0.0.1" -fsS --connect-timeout 3 --max-time 8 -H "Authorization: Bearer ${NODE_TOKEN_ID}.${NODE_TOKEN}" "$HEALTH_URL" >/dev/null || {
            journalctl -u wings -n 80 --no-pager
            systemctl stop wings || true
            fail "Wings started but its authenticated API health check failed. See $LOG_FILE and journalctl -u wings."
        }
    else
        curl -fsS --connect-timeout 3 --max-time 8 -H "Authorization: Bearer ${NODE_TOKEN_ID}.${NODE_TOKEN}" "$HEALTH_URL" >/dev/null || {
            journalctl -u wings -n 80 --no-pager
            systemctl stop wings || true
            fail "Wings started but its authenticated API health check failed. See $LOG_FILE and journalctl -u wings."
        }
    fi
    rm -f "$CONFIG_FILE"
    log "Wings API authenticated successfully for local node $NODE_ID."
    log "Open TCP 8080 (node API) and 2022 (SFTP) in the VPS/cloud firewall as required."
    exit 0
fi

[[ $# -eq 0 ]] || fail "Unexpected arguments."
[[ "$EUID" -eq 0 ]] || fail "Run through the restricted sudo rule."
IFS= read -r NODE_ID || fail "Node ID was not supplied on stdin."
IFS= read -r NODE_HOST || fail "Node hostname was not supplied on stdin."
[[ "$NODE_ID" =~ ^[0-9]+$ ]] || fail "Invalid node ID."
[[ "$NODE_HOST" =~ ^[a-zA-Z0-9][a-zA-Z0-9.:_-]*[a-zA-Z0-9]$ ]] || fail "Invalid node hostname."
install -d -o root -g root -m 0700 "$CONFIG_ROOT"
CONFIG_FILE="$CONFIG_ROOT/config-$NODE_ID.yml"
[[ ! -e "$CONFIG_FILE" ]] || fail "A local setup configuration already exists for node $NODE_ID."
umask 077
cat >"$CONFIG_FILE"
[[ -s "$CONFIG_FILE" ]] || { rm -f "$CONFIG_FILE"; fail "Empty node configuration."; }
grep -q '^uuid:' "$CONFIG_FILE" || { rm -f "$CONFIG_FILE"; fail "Node UUID is missing from configuration."; }
grep -q '^token_id:' "$CONFIG_FILE" || { rm -f "$CONFIG_FILE"; fail "Node token ID is missing from configuration."; }
grep -q '^remote:' "$CONFIG_FILE" || { rm -f "$CONFIG_FILE"; fail "Panel URL is missing from configuration."; }
grep -q '^api:' "$CONFIG_FILE" || { rm -f "$CONFIG_FILE"; fail "Node API configuration is missing."; }
chmod 0600 "$CONFIG_FILE"
UNIT="snck-local-node-setup-$NODE_ID-$(date +%s)"
systemd-run --quiet --no-block --unit="$UNIT" --property=Type=oneshot --property=TimeoutStartSec=900 "$HELPER" --run "$NODE_ID" "$NODE_HOST"
log "Background Wings setup started as $UNIT."
