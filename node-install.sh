#!/usr/bin/env bash
set -Eeuo pipefail

PANEL_URL=""
NODE_ID=""
SETUP_TOKEN=""
NODE_PORT="${NODE_PORT:-6768}"
NODE_DIR="${SHIRONEX_NODE_DIR:-/opt/shironex-node}"
CONFIG_DIR="/etc/shironex-node"
DATA_DIR="/var/lib/shironex/servers"
SERVICE="shironex-node"
DAEMON_VERSION="1.1.0"

info(){ printf '\033[1;36m[INFO]\033[0m %s\n' "$*"; }
ok(){ printf '\033[1;32m[ OK ]\033[0m %s\n' "$*"; }
fail(){ printf '\033[1;31m[ERROR]\033[0m %s\n' "$*" >&2; exit 1; }

usage(){
  cat <<'EOF'
ShiroNex Node Installer

Usage:
  sudo bash node-install.sh \
    --panel https://panel.example.com \
    --node-id NODE_ID \
    --setup-token ONE_TIME_TOKEN \
    --port 6768
EOF
}

require_root(){ [[ "$EUID" -eq 0 ]] || fail "Run with sudo/root."; }
check_os(){
  [[ -r /etc/os-release ]] || fail "Cannot detect operating system."
  . /etc/os-release
  case "$ID" in
    ubuntu)
      [[ -n "${VERSION_ID:-}" ]] || fail "Ubuntu version could not be detected."
      if dpkg --compare-versions "$VERSION_ID" lt "20.04"; then
        fail "This ShiroNex node requires Ubuntu 20.04 or newer. Detected: Ubuntu $VERSION_ID. Upgrade the VPS to Ubuntu 20.04+ and run the installer again."
      fi
      if [[ "$VERSION_ID" != "22.04" && "$VERSION_ID" != "24.04" ]]; then
        printf '\033[1;33m[WARN]\033[0m Ubuntu %s is not one of the primary CI-tested releases; continuing with the generic Ubuntu compatibility path.\n' "$VERSION_ID"
      fi
      ;;
    debian)
      case "${VERSION_ID:-}" in
        11|12|13) ;;
        *) fail "Supported Debian versions: 11, 12, and 13. Detected: ${PRETTY_NAME:-$ID}" ;;
      esac
      ;;
    *) fail "Supported OS: Ubuntu 20.04+ or Debian 11/12/13. Detected: ${PRETTY_NAME:-$ID}" ;;
  esac
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --panel) PANEL_URL="${2:-}"; shift 2 ;;
    --node-id) NODE_ID="${2:-}"; shift 2 ;;
    --setup-token) SETUP_TOKEN="${2:-}"; shift 2 ;;
    --port) NODE_PORT="${2:-}"; shift 2 ;;
    -h|--help) usage; exit 0 ;;
    *) fail "Unknown argument: $1" ;;
  esac
done

require_root
check_os
[[ -n "$PANEL_URL" && -n "$NODE_ID" && -n "$SETUP_TOKEN" ]] || { usage; exit 2; }
[[ "$PANEL_URL" =~ ^https?:// ]] || fail "Panel URL must start with http:// or https://."
[[ "$NODE_PORT" =~ ^[0-9]+$ && "$NODE_PORT" -ge 1024 && "$NODE_PORT" -le 65535 ]] || fail "Invalid node port."

apt-get update
DEBIAN_FRONTEND=noninteractive apt-get install -y ca-certificates curl openssl git build-essential xz-utils

if ! command -v node >/dev/null 2>&1 || [[ "$(node -p 'process.versions.node.split(".")[0]')" -lt 20 ]]; then
  if ! (curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs); then
    printf '\033[1;33m[WARN]\033[0m NodeSource does not publish packages for this Ubuntu release; installing the official Node.js binary instead.\n'
    NODE_ARCH=""
    case "$(dpkg --print-architecture)" in
      amd64) NODE_ARCH="x64" ;;
      arm64) NODE_ARCH="arm64" ;;
      *) fail "No official Node.js binary is available for architecture: $(dpkg --print-architecture)" ;;
    esac
    NODE_VERSION="22.14.0"
    NODE_ROOT="/opt/nodejs/node-v${NODE_VERSION}-linux-${NODE_ARCH}"
    rm -rf "$NODE_ROOT"
    curl -fsSL "https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-${NODE_ARCH}.tar.xz" -o /tmp/shironex-node.tar.xz
    install -d -m 755 /opt/nodejs
    tar -xJf /tmp/shironex-node.tar.xz -C /opt/nodejs
    ln -sfn "$NODE_ROOT/bin/node" /usr/local/bin/node
    ln -sfn "$NODE_ROOT/bin/npm" /usr/local/bin/npm
    ln -sfn "$NODE_ROOT/bin/npx" /usr/local/bin/npx
    rm -f /tmp/shironex-node.tar.xz
  fi
fi

NODE_BIN="$(command -v node)"
NPM_BIN="$(command -v npm)"
[[ -x "$NODE_BIN" ]] || fail "Node.js installation failed."
[[ -x "$NPM_BIN" ]] || fail "npm installation failed."
NODE_MAJOR="$($NODE_BIN -p 'Number(process.versions.node.split(".")[0])')"
(( NODE_MAJOR >= 20 )) || fail "Node.js 20 or newer is required; found $($NODE_BIN --version)."

if ! command -v docker >/dev/null 2>&1; then
  DOCKER_READY=false
  install -m 0755 -d /etc/apt/keyrings
  if curl -fsSL "https://download.docker.com/linux/${ID}/gpg" -o /etc/apt/keyrings/docker.asc; then
    chmod a+r /etc/apt/keyrings/docker.asc
    . /etc/os-release
    cat >/etc/apt/sources.list.d/docker.sources <<EOF
Types: deb
URIs: https://download.docker.com/linux/${ID}
Suites: ${VERSION_CODENAME:-${UBUNTU_CODENAME:-}}
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF
    if apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin; then
      DOCKER_READY=true
    else
      printf '\033[1;33m[WARN]\033[0m Docker upstream packages are unavailable; using distribution Docker packages.\n'
    fi
  fi
  if [[ "$DOCKER_READY" != true ]]; then
    rm -f /etc/apt/sources.list.d/docker.sources
    apt-get update
    DEBIAN_FRONTEND=noninteractive apt-get install -y docker.io
    DEBIAN_FRONTEND=noninteractive apt-get install -y docker-compose-v2 2>/dev/null || true
  fi
fi
if command -v systemctl >/dev/null 2>&1 && [[ -d /run/systemd/system ]]; then systemctl enable --now docker || true; fi
docker info >/dev/null 2>&1 || fail "Docker is not ready."

install -d -m 700 "$NODE_DIR" "$CONFIG_DIR" "$DATA_DIR"

info "Downloading ShiroNex node daemon..."
TMP="$(mktemp -d /tmp/shironex-node.XXXXXX)"
trap 'rm -rf "$TMP"' EXIT

curl -fsSL "$PANEL_URL/shironex-node.tar.gz" -o "$TMP/node.tar.gz" \
  || fail "Could not download the node daemon from $PANEL_URL."

tar -xzf "$TMP/node.tar.gz" -C "$NODE_DIR"
cd "$NODE_DIR"
"$NPM_BIN" ci --no-audit --no-fund 2>/dev/null || "$NPM_BIN" install --no-audit --no-fund

info "Registering node..."
RESPONSE="$(curl -fsS --retry 3 \
  -X POST "$PANEL_URL/api/node-agent/register" \
  -H 'content-type: application/json' \
  -d "{\"nodeId\":\"$NODE_ID\",\"setupToken\":\"$SETUP_TOKEN\",\"daemonVersion\":\"$DAEMON_VERSION\"}")" \
  || fail "Node registration failed."

CREDENTIAL="$(printf '%s' "$RESPONSE" | "$NODE_BIN" -e '
let s="";
process.stdin.on("data",d=>s+=d).on("end",()=>{
  try {
    const j=JSON.parse(s);
    if(!j.credential) process.exit(2);
    process.stdout.write(j.credential);
  } catch {
    process.exit(3);
  }
})' 2>/dev/null)" || fail "Panel did not return a node credential."

"$NODE_BIN" -e '
const fs=require("fs");
const path=process.argv[1];
const obj=JSON.parse(process.argv[2]);
fs.writeFileSync(path, JSON.stringify(obj)+"\n", {mode:0o600});
' "$CONFIG_DIR/config.json" \
  "{\"panelUrl\":\"$PANEL_URL\",\"nodeId\":\"$NODE_ID\",\"credential\":\"$CREDENTIAL\",\"port\":$NODE_PORT,\"serverDirectory\":\"$DATA_DIR\",\"dockerSocket\":\"/var/run/docker.sock\",\"heartbeatIntervalMs\":10000,\"daemonVersion\":\"$DAEMON_VERSION\"}"

"$NPM_BIN" run build

cat >"/etc/systemd/system/$SERVICE.service" <<EOF
[Unit]
Description=ShiroNex Node Daemon
After=docker.service network-online.target
Wants=network-online.target
Requires=docker.service

[Service]
Type=simple
ExecStart=$NODE_BIN $NODE_DIR/dist/index.js
Restart=always
RestartSec=3
User=root
LimitNOFILE=65535

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable --now "$SERVICE"
systemctl is-active --quiet "$SERVICE" || fail "Node service failed. Check: journalctl -u $SERVICE -n 100 --no-pager"

ok "ShiroNex node installed and online."
echo "Node service: systemctl status $SERVICE --no-pager"
echo "Node logs:    journalctl -u $SERVICE -f"
