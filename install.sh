#!/usr/bin/env bash
set -Eeuo pipefail

# One-command SNCK PANEL bootstrapper.
# It always opens the interactive control center instead of installing immediately.
MENU_URL="${SNCK_MENU_URL:-https://raw.githubusercontent.com/SnckBoy/snck-panel/main/scripts/snck-menu.sh}"

# Pick a writable temporary directory without changing permissions on shared dirs.
choose_tmp_dir() {
  local dir probe
  for dir in "${TMPDIR:-}" /root/.cache/snck-panel/tmp /var/tmp /tmp; do
    [[ -n "$dir" ]] || continue
    if [[ "$dir" == "/root/.cache/snck-panel/tmp" ]]; then
      mkdir -p "$dir" 2>/dev/null || continue
      chmod 700 "$dir" 2>/dev/null || continue
    fi
    [[ -d "$dir" && -w "$dir" ]] || continue
    probe="$(mktemp "$dir/.snck-probe.XXXXXX" 2>/dev/null)" || continue
    rm -f -- "$probe"
    printf '%s' "$dir"
    return 0
  done
  return 1
}

if [[ "$EUID" -ne 0 ]]; then
  if [[ -r /dev/tty ]]; then
    exec sudo -E bash -c '
      set -Eeuo pipefail
      url="$1"; shift
      tmp_base="/root/.cache/snck-panel/tmp"
      mkdir -p "$tmp_base"
      chmod 700 "$tmp_base"
      tmp_file="$(mktemp "$tmp_base/snck-menu.XXXXXX")"
      trap "rm -f -- \"$tmp_file\"" EXIT
      curl -fsSL --retry 3 --connect-timeout 10 --max-time 120 "$url" -o "$tmp_file"
      chmod 700 "$tmp_file"
      bash "$tmp_file" "$@" </dev/tty
    ' -- "$MENU_URL" "$@"
  fi
  printf 'ERROR: An interactive terminal is required. Run this from an SSH/Termius terminal.\n' >&2
  exit 2
fi

TMP_BASE="$(choose_tmp_dir)" || { printf 'ERROR: No writable temporary directory found (checked TMPDIR, /var/tmp, /root/.cache/snck-panel/tmp, /tmp).\\n' >&2; exit 1; }
TMP="$(mktemp "$TMP_BASE/snck-bootstrap.XXXXXX")"
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
