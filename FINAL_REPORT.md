# SNCK Panel Laravel Migration — Final Report

**Date:** 2026-11-01  
**Branch:** `php-migration-final`  
**Pull request:** #39  
**Legacy rollback tag:** `legacy-node-final`

## What was built

The Laravel release candidate now covers the original panel's authenticated foundation, Owner setup, PostgreSQL persistence, idempotent legacy JSON import, server provisioning and lifecycle, files, console/logs, players, properties, backups, sub-users, nodes, allocations, themes, Cloudflare DNS, Playit, plugin/mod/modpack management, SFTP, administrator settings/users, API keys, and UI parity. `UI_PARITY.md` is the page-by-page checklist.

The UI parity batch added an AdminWorkspace Livewire page for Settings, Cloudflare, API keys, and Discord configuration, and completed the missing ServerDetail tabs for SFTP, plugins, mods, modpacks, and Playit. Existing console, logs, players, properties, files, backups, and sub-user interfaces remain wired to Laravel services and the frozen daemon contract.

SFTP is now a real SSH/SFTP transport in `node-daemon/src/sftp.ts`. The daemon calls Laravel for node-authenticated password validation, maps the authenticated account to its server root, and blocks absolute paths, `..` traversal, symlink escapes, and unsafe handles. Passwords remain hashed in Laravel and are returned only by create/reset operations.

The production installer, `/install` web installer, `scripts/smoke-test.sh`, updated runbook, release documentation, and `legacy-node-final` tag are included.

## Verification

| Check | Result |
|---|---:|
| Complete Laravel PostgreSQL suite | **67 passed / 361 assertions** |
| SFTP Laravel feature tests | **3 passed / 17 assertions** |
| Node-daemon SFTP jail tests | **2 passed** |
| Laravel Pint | **100 files pass** |
| Larastan | **No errors** with baseline for pre-existing migration debt |
| Composer audit | **No advisories** |
| Laravel frontend build | **Pass** |
| Node-daemon TypeScript build | **Pass** |
| Blade cache and route compilation | **Pass** |
| Smoke-test shell syntax | **Pass** |

## Exact install steps

On Ubuntu/Debian with PHP 8.3, Node.js 20+, Composer, PostgreSQL access, and systemd:

```bash
git clone --branch php-migration-final https://github.com/SnckBoy/snck-panel.git /opt/snck-panel
cd /opt/snck-panel
sudo bash scripts/install-laravel-panel.sh --dry-run
sudo bash scripts/install-laravel-panel.sh --app-dir /opt/snck-panel/laravel-panel --repo https://github.com/SnckBoy/snck-panel.git --branch php-migration-final
```

Set the PostgreSQL values in `/opt/snck-panel/laravel-panel/.env`, place the daemon configuration and SFTP host key on each node, then open `/install` through HTTPS to create the first Owner. Provision a server SFTP credential from its SFTP tab and test it with an external client. Finally run:

```bash
BASE_URL=https://panel.example.test bash scripts/smoke-test.sh
```

## What still needs real-node testing

The sandbox cannot prove Docker image pulls and container lifecycle, daemon TLS/heartbeat against a remote node, production filesystem ownership, external SFTP login and file operations, Playit/PM2 operation, Cloudflare DNS changes with a real token, Nginx/PHP-FPM/systemd restarts, queue/scheduler execution, PostgreSQL backup restoration, or importer results against a real production backup. These are mandatory staging gates before cutover.

## Remaining engineering scope

Discord live OAuth/account linking, gateway invite attribution, reward claiming and provisioning, queue/systemd worker lifecycle, retention cleanup, member reconciliation, moderation/review/reconcile actions, and their complete reward UI remain open. The Laravel branch currently provides encrypted Discord configuration and strict reward-plan administration; it does not claim the full live Discord rewards system is complete.

## Recommendation

Approve the branch for staging review and external-node testing. Do not perform production cutover until the real-node gates above pass, the importer and restore rehearsal are accepted, and the remaining Discord scope is either completed or explicitly removed from the release acceptance criteria.
