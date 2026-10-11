#!/usr/bin/env bash
set -Eeuo pipefail

# One-command SNCK PANEL bootstrapper.
# It always opens the interactive control center instead of installing immediately.
MENU_URL="${SNCK_MENU_URL:-https://raw.githubusercontent.com/SnckBoy/snck-panel/main/scripts/snck-menu.sh}"

if [[ "$EUID" -ne 0 ]]; then
  if [[ -r /dev/tty ]]; then
    exec sudo -E bash -c '
      set -Eeuo pipefail
      url="$1"; shift
      curl -fsSL --retry 3 --connect-timeout 10 --max-time 120 "$url" -o /tmp/snck-menu.sh
      chmod 700 /tmp/snck-menu.sh
      exec bash /tmp/snck-menu.sh "$@" </dev/tty
    ' -- "$MENU_URL" "$@"
  fi
  printf 'ERROR: An interactive terminal is required. Run this from an SSH/Termius terminal.\n' >&2
  exit 2
fi

TMP="$(mktemp)"
trap 'rm -f "$TMP"' EXIT
curl -fsSL --retry 3 --connect-timeout 10 --max-time 120 "$MENU_URL" -o "$TMP"
chmod 700 "$TMP"

# `curl ... | bash` gives the outer shell the pipe on stdin. The menu needs
# the real terminal for `read`, so explicitly reconnect it when available.
if [[ -r /dev/tty ]]; then
  exec bash "$TMP" "$@" </dev/tty
else
  printf 'ERROR: No interactive terminal found. Run from SSH/Termius terminal.\n' >&2
  exit 2
fi
