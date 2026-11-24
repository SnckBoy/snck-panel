#!/usr/bin/env bash
set -Eeuo pipefail

WINGS_VERSION="v1.13.5"
PANEL_URL="${SNCK_PANEL_URL:-}"
NODE_ID="${SNCK_NODE_ID:-}"
NODE_TOKEN="${SNCK_NODE_TOKEN:-}"

log() { printf '\033[1;36m[SNCK-WINGS]\033[0m %s\n' "$*"; }
fail() { printf '\033[1;31m[ERROR]\033[0m %s\n' "$*" >&2; exit 1; }

usage() {
  cat <<'EOF'
SNCK Wings node installer

Usage:
  curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/feat/snck-pterodactyl-v1.15.2/node-install.sh | sudo bash -s -- --panel-url https://panel.example.com --node-id 1

Options:
  --panel-url URL       SNCK Panel HTTPS URL
  --node-id ID          Numeric node ID from Admin > Nodes
  --help                Show this help

The installer asks for the one-time auto-deploy token without echoing it.
Create the node in the Panel first and use its Auto-Deploy token.
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --panel-url) PANEL_URL="${2:-}"; shift 2 ;;
    --node-id) NODE_ID="${2:-}"; shift 2 ;;
    -h|--help) usage; exit 0 ;;
    *) fail "Unknown argument: $1" ;;
  esac
done

[[ "$EUID" -eq 0 ]] || fail "Run as root (sudo)."
[[ -r /etc/os-release ]] || fail "Cannot detect operating system."
. /etc/os-release
case "${ID:-}" in
  ubuntu) [[ "${VERSION_ID:-}" == "20.04" || "${VERSION_ID:-}" == "22.04" || "${VERSION_ID:-}" == "24.04" ]] || fail "Supported Ubuntu versions: 20.04, 22.04 and 24.04." ;;
  debian) [[ "${VERSION_ID:-}" == "11" || "${VERSION_ID:-}" == "12" || "${VERSION_ID:-}" == "13" ]] || fail "Supported Debian versions: 11, 12 and 13." ;;
  *) fail "Supported node OS: Ubuntu 20.04/22.04/24.04 or Debian 11/12/13." ;;
esac

[[ "$PANEL_URL" == https://* ]] || fail "Panel URL must use HTTPS."
[[ "$NODE_ID" =~ ^[0-9]+$ ]] || fail "Node ID must be numeric."
[[ "$WINGS_VERSION" =~ ^v[0-9]+\.[0-9]+\.[0-9]+$ ]] || fail "Invalid Wings version."

if [[ -z "$NODE_TOKEN" ]]; then
  read -r -s -p "Paste the one-time node Auto-Deploy token: " NODE_TOKEN </dev/tty
  printf '\n'
fi
[[ -n "$NODE_TOKEN" ]] || fail "Node token is required."

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

export DEBIAN_FRONTEND=noninteractive
log "Installing Docker Engine and required utilities..."
apt-get update
apt-get install -y ca-certificates curl gnupg docker.io
systemctl enable --now docker

TMP="$(mktemp /tmp/snck-wings.XXXXXX)"
trap 'rm -f "$TMP"' EXIT
DOWNLOAD_URL="https://github.com/pterodactyl/wings/releases/download/${WINGS_VERSION}/${ASSET}"
log "Downloading Wings ${WINGS_VERSION} (${ASSET})..."
curl -fsSLo "$TMP" "$DOWNLOAD_URL"
printf '%s  %s\n' "$EXPECTED_SHA256" "$TMP" | sha256sum --check --status || fail "Wings SHA-256 verification failed."
install -m 0755 "$TMP" /usr/local/bin/wings

install -d -m 0755 /etc/pterodactyl
if [[ -s /etc/pterodactyl/config.yml ]]; then
  cp -a /etc/pterodactyl/config.yml "/etc/pterodactyl/config.yml.backup.$(date +%Y%m%d%H%M%S)"
  log "Existing Wings config backed up."
fi

log "Configuring Wings with the Panel's Auto-Deploy token..."
wings configure --panel-url "$PANEL_URL" --token "$NODE_TOKEN" --node "$NODE_ID" --override
unset NODE_TOKEN

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
sleep 2
systemctl is-active --quiet wings || { journalctl -u wings -n 80 --no-pager; fail "Wings did not start. Check the generated node config and Docker status."; }

log "Wings installed and running."
printf 'Node API and SFTP ports are defined in the generated config.yml.\n'
printf 'Ensure the node firewall and provider firewall allow the configured API, SFTP, and game allocation ports.\n'
printf 'Service status: systemctl status wings\n'
