# SNCK Panel discord-final report

## Delivered

This branch adds a complete staff Cloudflare DNS page with zone selection and CRUD for A, AAAA, CNAME, TXT, MX, and SRV records, including structured SRV editing and a Minecraft SRV helper.

Discord core now uses real Discord OAuth HTTP endpoints for authorization-code exchange and profile lookup. OAuth state is checked, access tokens are encrypted, accounts can be unlinked, users can see reward progress and submit duplicate-safe rate-limited claims, and staff can approve, reject, or reconcile claims with audit events.

The worker foundation adds real Discord guild-member reconciliation and retention cleanup through `discord:maintenance`, with a five-minute systemd timer added to the production installer. The exact UI status is documented in `UI_PARITY.md`.

## Verification

Item-focused verification completed:

- Cloudflare UI: 2 tests / 5 assertions.
- Discord core: 2 tests / 9 assertions.
- Discord maintenance worker: 1 test / 3 assertions.
- Pint and Blade cache passed during each focused item.
- Installer shell syntax passed.

Final verification passed **70 tests / 330 assertions**, with 2 PostgreSQL-only importer tests skipped under the default SQLite configuration. Pint passes 105 files, Larastan reports no errors, Composer audit is clean, Blade cache passes, and installer shell syntax passes.

## Installation

```bash
git clone --branch discord-final https://github.com/SnckBoy/snck-panel.git /opt/snck-panel
cd /opt/snck-panel
sudo bash scripts/install-laravel-panel.sh --dry-run
sudo bash scripts/install-laravel-panel.sh --app-dir /opt/snck-panel/laravel-panel --repo https://github.com/SnckBoy/snck-panel.git --branch discord-final
```

Configure PostgreSQL and Discord OAuth values in `.env`/the admin page, then open `/install` to create the first Owner. Configure the Discord OAuth redirect URI to `/auth/discord/callback`, the Discord bot token and guild ID, and run `BASE_URL=https://panel.example.test bash scripts/smoke-test.sh` after deployment.

## Remaining work

A persistent WebSocket Gateway worker for invite attribution and automatic reward provisioning through `ServerProvisioner` is not complete. Claims are currently recorded for staff review; automatic provisioning is intentionally not claimed. These are the only Discord items marked `PARTIAL` in `UI_PARITY.md` and must be completed before describing Discord rewards as fully automated.


## PR #42 integration work — SFTP and administrator workspace

The php-migration-final PR adds the staff-only AdminWorkspace Livewire page for persisted settings, Cloudflare configuration, API-key management, and Discord administration configuration. It also adds the SFTP credential-authentication callback and a node-side SSH/SFTP listener using the ssh2 package. SFTP requests use the SSH2 server protocol, validate the node bearer credential against Laravel, and resolve each authenticated account to its own server directory. Absolute virtual paths are rooted inside the jail; traversal and symlink components are rejected.

A real SSH/SFTP upload/download integration test has been added. The test and the complete combined Laravel/PostgreSQL and node-daemon suites must be run on the updated branch before this report can claim final verification. The main-branch Discord verification numbers above predate the PR #42 additions and are not a combined-suite result.
