#!/usr/bin/env bash
set -Eeuo pipefail

# Root entry point for the Laravel/PHP panel. The branch defaults to main;
# use SNCK_BRANCH=... to install a different branch explicitly.
BRANCH="${SNCK_BRANCH:-main}"
REPO_URL="${SNCK_REPO_URL:-https://github.com/SnckBoy/snck-panel.git}"
FORWARD_ARGS=()

while [[ $# -gt 0 ]]; do
  case "$1" in
    --branch=*) BRANCH="${1#*=}"; shift ;;
    --repo=*) REPO_URL="${1#*=}"; shift ;;
    -h|--help)
      cat <<'EOF'
SNCK Panel — Laravel installer entry point

Usage:
  curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/main/install.sh | sudo bash

Options:
  --branch=BRANCH
  --repo=GIT_URL
  --dry-run
  --app-dir=/absolute/path
  --help

Prerequisites: PHP 8.3+, Composer, Node.js 20+, npm, and a configured PostgreSQL database.
EOF
      exit 0
      ;;
    *) FORWARD_ARGS+=("$1"); shift ;;
  esac
done
INSTALLER_URL="${SNCK_INSTALLER_URL:-https://raw.githubusercontent.com/SnckBoy/snck-panel/$BRANCH/scripts/install-laravel-panel.sh}"

if [[ "$EUID" -ne 0 ]]; then
  command -v sudo >/dev/null 2>&1 || { echo 'ERROR: sudo is required. Run as root.' >&2; exit 1; }
fi
command -v curl >/dev/null 2>&1 || { echo 'ERROR: curl is required.' >&2; exit 1; }
TMP="$(mktemp /tmp/snck-panel-laravel-installer.XXXXXX)"
trap 'rm -f -- "$TMP"' EXIT
curl -fsSL --retry 3 --connect-timeout 10 --max-time 120 "$INSTALLER_URL" -o "$TMP"
chmod 700 "$TMP"

if [[ "$EUID" -eq 0 ]]; then
  SNCK_REPO_URL="$REPO_URL" bash "$TMP" --branch="$BRANCH" "${FORWARD_ARGS[@]}"
else
  PRESERVE_ENV="SNCK_DB_HOST,SNCK_DB_PORT,SNCK_DB_DATABASE,SNCK_DB_USERNAME,SNCK_DB_PASSWORD,SNCK_DB_SSLMODE,SNCK_APP_URL,SNCK_PANEL_DIR"
  sudo --preserve-env="$PRESERVE_ENV" env SNCK_REPO_URL="$REPO_URL" bash "$TMP" --branch="$BRANCH" "${FORWARD_ARGS[@]}"
fi
