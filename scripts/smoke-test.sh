#!/usr/bin/env bash
set -u
BASE_URL="${BASE_URL:-http://127.0.0.1}"
COOKIE_FILE="${COOKIE_FILE:-$(mktemp)}"
trap 'rm -f "$COOKIE_FILE"' EXIT
passed=0
failed=0
check() {
  local name="$1" url="$2" expected="$3" actual body
  body="$(mktemp)"
  actual="$(curl --silent --show-error --location --max-time "${CURL_TIMEOUT:-10}" --output "$body" --write-out '%{http_code}' --cookie-jar "$COOKIE_FILE" --cookie "$COOKIE_FILE" "$BASE_URL$url" || printf '000')"
  if [[ "$actual" == "$expected" ]]; then printf 'PASS %-32s HTTP %s\n' "$name" "$actual"; passed=$((passed + 1)); else printf 'FAIL %-32s expected HTTP %s, got %s\n' "$name" "$expected" "$actual"; failed=$((failed + 1)); fi
  rm -f "$body"
}

check "health endpoint" "/health" "200"
check "login page" "/login" "200"
check "setup page or redirect" "/setup" "200"
check "install alias" "/install" "200"
check "protected dashboard redirect" "/dashboard" "302"
check "protected servers API" "/api/servers" "302"
check "protected API keys" "/api/api-keys" "302"
check "protected node API" "/api/nodes" "302"

printf '\nSmoke result: %d passed, %d failed\n' "$passed" "$failed"
if (( failed > 0 )); then exit 1; fi
