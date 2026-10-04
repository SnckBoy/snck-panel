#!/usr/bin/env bash
set -Eeuo pipefail

# One-command SNCK PANEL bootstrapper. It intentionally opens the interactive
# control center instead of installing immediately.
MENU_URL="${SNCK_MENU_URL:-https://raw.githubusercontent.com/SnckBoy/snck-panel/main/scripts/snck-menu.sh}"

if [[ "$EUID" -ne 0 ]]; then
  exec sudo -E bash -c 'curl -fsSL --retry 3 --connect-timeout 10 --max-time 120 "$1" | bash' -- "$MENU_URL"
fi

TMP="$(mktemp)"
trap 'rm -f "$TMP"' EXIT
curl -fsSL --retry 3 --connect-timeout 10 --max-time 120 "$MENU_URL" -o "$TMP"
chmod 700 "$TMP"
exec bash "$TMP" "$@"
