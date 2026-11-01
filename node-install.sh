#!/usr/bin/env bash
set -Eeuo pipefail

PANEL_URL=""
NODE_ID=""
NODE_FQDN=""
NODE_CREDENTIAL=""
NODE_PORT="${NODE_PORT:-6768}"
SFTP_PORT="${SFTP_PORT:-2022}"
NODE_SOURCE_URL="${SHIRONEX_NODE_SOURCE_URL:-https://github.com/SnckBoy/snck-panel/archive/refs/heads/main.tar.gz}"
NODE_DIR="${SHIRONEX_NODE_DIR:-/opt/shironex-node}"
CONFIG_DIR="/etc/shironex-node"
DATA_DIR="/var/lib/shironex/servers"
SERVICE="shironex-node"

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
    --fqdn node.example.com \
    --credential NODE_BEARER_TOKEN \
    --port 6768 \
    --sftp-port 2022

The node must have a DNS A/AAAA record pointing to this VPS.
The installer obtains a trusted TLS certificate using HTTP-01 on port 80.
The daemon uses HTTPS; SFTP uses SSH on the configured SFTP port.
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
        fail "This ShiroNex node requires Ubuntu 20.04 or newer. Detected: Ubuntu $VERSION_ID."
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
    --fqdn) NODE_FQDN="${2:-}"; shift 2 ;;
    --credential) NODE_CREDENTIAL="${2:-}"; shift 2 ;;
    --port) NODE_PORT="${2:-}"; shift 2 ;;
    --sftp-port) SFTP_PORT="${2:-}"; shift 2 ;;
    --source-url) NODE_SOURCE_URL="${2:-}"; shift 2 ;;
    -h|--help) usage; exit 0 ;;
    *) fail "Unknown argument: $1" ;;
  esac
done

require_root
check_os
[[ -n "$PANEL_URL" && -n "$NODE_ID" && -n "$NODE_FQDN" && -n "$NODE_CREDENTIAL" ]] || { usage; exit 2; }
PANEL_URL="${PANEL_URL%/}"
NODE_FQDN="${NODE_FQDN,,}"
[[ "$PANEL_URL" == https://* && "$PANEL_URL" != *$'\n'* ]] || fail "Panel URL must use public HTTPS."
[[ "$NODE_ID" =~ ^[a-zA-Z0-9_-]{1,64}$ ]] || fail "Invalid node ID."
[[ "$NODE_FQDN" =~ ^([a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$ ]] || fail "Provide a valid public DNS name for the node."
[[ "$NODE_CREDENTIAL" =~ ^[A-Za-z0-9_-]{32,512}$ ]] || fail "Node bearer token must be 32-512 URL-safe characters."
[[ "$NODE_PORT" =~ ^[0-9]+$ && "$NODE_PORT" -ge 1024 && "$NODE_PORT" -le 65535 ]] || fail "Invalid node HTTPS port."
[[ "$SFTP_PORT" =~ ^[0-9]+$ && "$SFTP_PORT" -ge 1024 && "$SFTP_PORT" -le 65535 ]] || fail "Invalid SFTP port."
[[ "$NODE_PORT" != "$SFTP_PORT" ]] || fail "Node HTTPS and SFTP ports must be different."
[[ "$NODE_SOURCE_URL" =~ ^https://github\.com/SnckBoy/snck-panel/archive/refs/heads/[A-Za-z0-9._/-]+\.tar\.gz$ ]] || fail "Node source must be a SnckBoy/snck-panel GitHub branch archive over HTTPS."

getent ahosts "$NODE_FQDN" >/dev/null 2>&1 || fail "Node FQDN does not resolve. Create its DNS A/AAAA record before installing."

apt-get update
DEBIAN_FRONTEND=noninteractive apt-get install -y ca-certificates curl openssl openssh-client certbot git build-essential xz-utils

if ! command -v node >/dev/null 2>&1 || [[ "$(node -p 'process.versions.node.split(".")[0]')" -lt 20 ]]; then
  if ! (curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs); then
    printf '\033[1;33m[WARN]\033[0m NodeSource does not publish packages for this release; installing the official Node.js binary instead.\n'
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
[[ -x "$NODE_BIN" && -x "$NPM_BIN" ]] || fail "Node.js/npm installation failed."
NODE_MAJOR="$("$NODE_BIN" -p 'Number(process.versions.node.split(".")[0])')"
(( NODE_MAJOR >= 20 )) || fail "Node.js 20 or newer is required; found $("$NODE_BIN" --version)."

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

info "Obtaining a trusted TLS certificate for $NODE_FQDN..."
certbot certonly --standalone --non-interactive --agree-tos --register-unsafely-without-email \
  --preferred-challenges http --keep-until-expiring -d "$NODE_FQDN" \
  || fail "Let's Encrypt certificate issuance failed. Ensure DNS points to this VPS and TCP port 80 is reachable."

TLS_KEY="/etc/letsencrypt/live/$NODE_FQDN/privkey.pem"
TLS_CERT="/etc/letsencrypt/live/$NODE_FQDN/fullchain.pem"
[[ -r "$TLS_KEY" && -r "$TLS_CERT" ]] || fail "TLS certificate files are missing after issuance."

HOST_KEY="$CONFIG_DIR/ssh_host_ed25519_key"
if [[ ! -s "$HOST_KEY" ]]; then
  ssh-keygen -q -t ed25519 -N "" -f "$HOST_KEY"
  chmod 600 "$HOST_KEY"
fi

info "Downloading node daemon source..."
TMP="$(mktemp -d /tmp/shironex-node.XXXXXX)"
trap 'rm -rf "$TMP"' EXIT
curl -fsSL --retry 3 --retry-delay 2 "$NODE_SOURCE_URL" -o "$TMP/repository.tar.gz" \
  || fail "Could not download the node daemon source archive."
tar -xzf "$TMP/repository.tar.gz" -C "$TMP"
NODE_PACKAGE_FILE="$(find "$TMP" -type f -path '*/node-daemon/package.json' -print -quit)"
[[ -n "$NODE_PACKAGE_FILE" ]] || fail "The source archive does not contain node-daemon/package.json."
NODE_SOURCE_DIR="$(dirname "$NODE_PACKAGE_FILE")"
cp -a "$NODE_SOURCE_DIR"/. "$NODE_DIR"/
cd "$NODE_DIR"
"$NPM_BIN" ci --no-audit --no-fund
"$NPM_BIN" run build

info "Writing private node configuration..."
"$NODE_BIN" -e '
const fs = require("node:fs");
const [target, panelUrl, nodeId, credential, port, sftpPort, serverDirectory, tlsKey, tlsCert, sftpHostKey] = process.argv.slice(1);
const daemonVersion = JSON.parse(fs.readFileSync("package.json", "utf8")).version;
const config = {
  panelUrl, nodeId, credential,
  port: Number(port), sftpPort: Number(sftpPort),
  serverDirectory, dockerSocket: "/var/run/docker.sock",
  heartbeatIntervalMs: 10000, daemonVersion,
  tlsKey, tlsCert, sftpHostKey
};
fs.writeFileSync(target, JSON.stringify(config) + "\n", { mode: 0o600 });
' "$CONFIG_DIR/config.json" "$PANEL_URL" "$NODE_ID" "$NODE_CREDENTIAL" "$NODE_PORT" "$SFTP_PORT" "$DATA_DIR" "$TLS_KEY" "$TLS_CERT" "$HOST_KEY"
chmod 600 "$CONFIG_DIR/config.json"

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

install -d -m 755 /etc/letsencrypt/renewal-hooks/deploy
cat >/etc/letsencrypt/renewal-hooks/deploy/restart-shironex-node <<'EOF'
#!/usr/bin/env bash
systemctl try-restart shironex-node.service
EOF
chmod 755 /etc/letsencrypt/renewal-hooks/deploy/restart-shironex-node

systemctl daemon-reload
systemctl enable --now "$SERVICE"
systemctl is-active --quiet "$SERVICE" || fail "Node service failed. Check: journalctl -u $SERVICE -n 100 --no-pager"
if systemctl list-unit-files certbot.timer >/dev/null 2>&1; then systemctl enable --now certbot.timer || true; fi

unset NODE_CREDENTIAL
ok "ShiroNex node installed with verified HTTPS and SFTP."
echo "Node HTTPS: https://$NODE_FQDN:$NODE_PORT"
echo "Node SFTP:  $NODE_FQDN:$SFTP_PORT"
echo "Open inbound TCP ports 80 (certificate renewal), $NODE_PORT (HTTPS daemon), and $SFTP_PORT (SFTP)."
echo "Node service: systemctl status $SERVICE --no-pager"
echo "Node logs:    journalctl -u $SERVICE -f"
