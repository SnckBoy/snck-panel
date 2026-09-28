#!/usr/bin/env bash
set -Eeuo pipefail

# Canonical SNCK PANEL installer.
# The historical ShiroNex-panel raw URL is intentionally kept compatible so
# existing deployment commands continue to install/update the current SNCK build.
CANONICAL_INSTALLER="${SNCK_INSTALLER_URL:-https://raw.githubusercontent.com/SnckBoy/snck-panel/main/scripts/install-panel.sh}"

if [[ "$EUID" -ne 0 ]]; then
  exec sudo -E bash -c 'curl -fsSL "$1" | bash' -- "$CANONICAL_INSTALLER"
fi

TMP="$(mktemp)"
trap 'rm -f "$TMP"' EXIT
curl -fsSL --retry 3 --connect-timeout 10 --max-time 120 "$CANONICAL_INSTALLER" -o "$TMP"
chmod 700 "$TMP"
exec bash "$TMP" "$@"
