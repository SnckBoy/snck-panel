# SNCK Panel Laravel Migration Plan

Updated 2026-11-01 for `php-migration-final` / PR #39.

## Completed scope

The Laravel 12 / PHP 8.3 application now includes PostgreSQL schema and legacy JSON import, authentication and Owner setup, server provisioning/lifecycle/files/backups, sub-users, nodes/allocations/heartbeat contracts, themes, Cloudflare DNS, Playit, plugins/mods/modpacks, administrator users/settings, UI parity, and an installed-panel smoke test.

`UI_PARITY.md` maps every original React page and user-visible feature to a Laravel Blade/Livewire surface. ServerDetail includes console, logs, players, properties, files, backups, sub-users, SFTP, plugins, mods, modpacks, and Playit. AdminWorkspace includes settings, Cloudflare, API keys, and Discord configuration.

SFTP is a real node-daemon SSH/SFTP subsystem. Laravel validates the username/password through the node-authenticated SFTP endpoint; the daemon maps the result to one server directory and rejects traversal, absolute paths, symlink escapes, and cross-server access.

## Exact installation steps

On a supported Ubuntu/Debian host with PHP 8.3, Node.js 20+, Composer, PostgreSQL access, and systemd:

```bash
git clone --branch php-migration-final https://github.com/SnckBoy/snck-panel.git /opt/snck-panel
cd /opt/snck-panel
sudo bash scripts/install-laravel-panel.sh --dry-run
sudo bash scripts/install-laravel-panel.sh --app-dir /opt/snck-panel/laravel-panel --repo https://github.com/SnckBoy/snck-panel.git --branch php-migration-final
```

Configure PostgreSQL credentials in `/opt/snck-panel/laravel-panel/.env`, then open `https://panel.example.test/install` behind TLS and create the first Owner. Configure each node with its daemon credential, `panelUrl`, `nodeId`, `serverDirectory`, `sftpHostKey`, and `sftpPort`. Provision an SFTP credential in the server SFTP tab and test with an external SFTP client. Run `BASE_URL=https://panel.example.test bash scripts/smoke-test.sh`.

## Release verification

The PostgreSQL suite passes **67 tests / 361 assertions**. SFTP-focused Laravel coverage passes **3 tests / 17 assertions**. Node-daemon SFTP jail tests pass **2 tests**. Pint passes 100 Laravel files, Larastan reports no errors with the checked-in pre-existing-debt baseline, Composer audit is clean, the Laravel production build passes, the node-daemon TypeScript build passes, and the smoke script passes shell syntax validation.

## Real-node staging gates

Before cutover, verify Docker image pull/provision/start/stop/restart, daemon TLS and authenticated heartbeat, node filesystem ownership, SFTP password login and upload/download/delete/traversal rejection, Playit agent/PM2, Cloudflare token and DNS CRUD, Nginx/PHP-FPM/systemd service restart, PostgreSQL backup/restore, queue/scheduler execution, and importer counts against a real backup. These cannot be proven by the sandbox test suite.

## Remaining scope

The remaining Discord production batch is live OAuth/account linking, gateway-based invite attribution, reward claiming and server/resource provisioning, queue/systemd worker lifecycle, retention cleanup, member reconciliation, moderation/review/reconcile endpoints, and complete user/admin reward UI. The current Discord batch provides strict encrypted configuration and plan CRUD; it must not be treated as a complete live rewards implementation until the remaining flows are ported and tested against Discord APIs.

The deprecated React/Express baseline remains available under the repository root and is tagged `legacy-node-final`; do not delete it until staging cutover and rollback rehearsal are approved.
