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


## Node provisioning completion

The Infrastructure page now creates the node record with an encrypted bearer credential and SHA-256 verification hash. If the administrator leaves the token field blank, the panel generates a 256-bit token. The page displays a one-time install command containing the matching node ID, FQDN, HTTPS panel URL, and credential; the command can be dismissed to clear it from the component state.

The node installer now matches that credential-based flow instead of calling the missing setup-token registration endpoint. It downloads the daemon source from the repository's main-branch archive, validates the node FQDN, obtains a Let's Encrypt certificate through HTTP-01 on TCP 80, generates a stable SSH host key, writes HTTPS/SFTP settings into the private daemon config, builds the daemon, and registers a systemd service. Certificate renewal restarts the daemon through a Certbot deploy hook.

CI run 41290717602 passed for the node provisioning changes, including Laravel/PHPUnit against PostgreSQL, Pint, PHPStan, frontend build, node-daemon TypeScript build, SSH/SFTP protocol tests, and shell syntax/help checks. Live DNS, firewall, certificate issuance/renewal, daemon heartbeat, Docker lifecycle, and SFTP behavior on a real VPS remain deployment-stage checks.

## Canonical Laravel installer

The root `install.sh` now fetches and executes `scripts/install-laravel-panel.sh` from the selected branch. The Laravel installer performs OS/PHP/Composer/Node preflight, validates the application path and public URL, prompts for PostgreSQL settings, preserves an existing `.env` and application key, updates dotenv values without sourcing the file, builds the Laravel frontend, runs migrations, configures a dedicated PHP-FPM pool and Nginx site, and installs queue/scheduler/Discord systemd units. It intentionally does not provision PostgreSQL, issue a panel TLS certificate, or configure Cloudflare Tunnel.

The installer no longer route-caches the app, and Laravel services load `.env` themselves rather than asking systemd to parse it. The root wrapper preserves explicit `SNCK_DB_*`, `SNCK_APP_URL`, and `SNCK_PANEL_DIR` environment values when it needs to elevate through sudo.