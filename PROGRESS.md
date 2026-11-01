# SNCK Panel migration progress

Updated 2026-11-01 on `php-migration-final` / PR #39.

## Completed migration batches

The Laravel 12 / PHP 8.3 implementation includes authentication and Owner setup, PostgreSQL schema and idempotent legacy JSON import, node heartbeat and HTTPS client contracts, server provisioning/lifecycle/files/backups, sub-users, themes, Cloudflare DNS, Playit, plugin/mod/modpack management, SFTP credentials, administrator users/settings, production installation, and regression coverage.

The requested UI parity pass is documented in [`UI_PARITY.md`](UI_PARITY.md). Every original React page and user-visible feature is mapped to a Laravel Blade/Livewire surface. ServerDetail now includes console, logs, players, properties, files, backups, sub-users, SFTP, plugins, mods, modpacks, and Playit tabs. AdminWorkspace provides settings, Cloudflare, API keys, and Discord configuration UI.

The SFTP transport is now implemented in `node-daemon/src/sftp.ts`. Passwords are validated by Laravel through the node-authenticated `/api/node-agent/sftp-auth` endpoint, and the daemon enforces a per-server jail with traversal and symlink escape rejection. Node-daemon build and SFTP security tests pass.

The installed-panel smoke script is [`scripts/smoke-test.sh`](scripts/smoke-test.sh). The legacy React/Express baseline has been tagged `legacy-node-final` at the current `main` commit for rollback and comparison.

## Verification

- PostgreSQL Laravel suite: **67 passed / 361 assertions**.
- SFTP-focused Laravel suite: **3 passed / 17 assertions**.
- Node-daemon SFTP tests: **2 passed**.
- Pint: **100 Laravel files pass** after the new UI/auth additions.
- Larastan: **no errors** with the checked-in baseline for existing migration debt.
- Composer audit: clean.
- Laravel frontend and node-daemon production builds: pass.
- Blade cache and route compilation: pass.
- Smoke script syntax: pass.

## Remaining release work

The remaining Discord production work is the live OAuth/account-linking flow, gateway-based invite attribution, reward provisioning, worker/systemd lifecycle, retention cleanup, reconciliation, moderation actions, and their complete user/admin UI. The current Laravel Discord batch has strict encrypted configuration and plan CRUD; it must not be described as a finished live rewards system until those flows are ported and tested against Discord HTTP APIs.

Real-node staging still must verify Docker provisioning, daemon TLS and heartbeat, node filesystem ownership, SFTP login with an external client, Playit/PM2, Cloudflare credentials, Nginx/PHP-FPM/systemd, PostgreSQL restore, and the installer on a supported host.

## Verification commands

```bash
cd laravel-panel
php artisan view:cache
php artisan test
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=5432 DB_DATABASE=snck_panel_test DB_USERNAME=snck_panel DB_PASSWORD=secret php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=1G
composer audit
npm ci --no-audit --no-fund && npm run build
cd ../node-daemon && npm run build && npm test
cd .. && bash -n scripts/smoke-test.sh
```
