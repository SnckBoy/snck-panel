# SNCK Panel

SNCK Panel is a self-hosted Minecraft server management panel. The **Laravel application in `laravel-panel/` is the PHP implementation and the intended primary panel** on this branch.

> **Branch status:** This documentation is for `repair/php-laravel-canonical`. The root `install.sh` now routes to the Laravel installer on the selected branch. The older React/Express application and its legacy scripts remain in the repository for migration/compatibility work.

## Project layout

| Path | Purpose |
| --- | --- |
| `laravel-panel/` | Laravel 12 application, Blade/Livewire UI, API routes, migrations and tests |
| `scripts/install-laravel-panel.sh` | CLI installer for the Laravel application |
| `scripts/` | Legacy/native panel scripts and utilities |
| `node-install.sh` | Existing ShiroNex node installer; its compatibility with the Laravel node client must be verified before production use |
| `.github/workflows/` | Repository CI workflows |

The older React/Vite + Express/TypeScript application remains in the repository for migration/compatibility work. It is not the PHP application's runtime.

## Main capabilities

The Laravel application currently contains routes and UI components for:

- User authentication, first-owner setup, account status and role-based administration.
- Dashboard and Minecraft server listing/creation/detail pages.
- Node and allocation administration.
- Server lifecycle actions, console commands, logs and resource statistics.
- Server file browsing, editing, upload/download, archive operations and backups.
- Plugin, mod and modpack management endpoints.
- Per-server subuser management and SFTP credential management.
- API key management.
- Discord reward configuration and claim administration.
- Cloudflare connection and DNS management screens.
- Theme management and export.

Feature availability depends on correct environment configuration and a compatible node daemon. A route or UI being present does not mean every external integration is already production-ready.

## Requirements

For the Laravel application:

- Ubuntu 22.04/24.04 or Debian (the installer checks for Ubuntu or Debian).
- PHP **8.3 or newer**.
- PHP extensions: `mbstring`, `openssl`, `pdo_pgsql`, and `zip` (Laravel may require additional standard PHP extensions depending on the enabled features).
- Composer.
- Node.js **20 or newer** and npm, for frontend asset compilation.
- PostgreSQL, with a database and user created for this application.
- Git and systemd for the CLI installer.
- Nginx and PHP-FPM are installed by the CLI installer.

The installer checks for PHP, Composer, Node.js and npm before it installs packages. Install those prerequisites first. It installs Nginx, PHP 8.3-FPM and the PostgreSQL client, but **does not install or provision a PostgreSQL server/database**.

## Database preparation

Create a PostgreSQL database and dedicated login role before running migrations. For example, on a PostgreSQL server where you have administrator access:

```sql
CREATE ROLE snck_panel LOGIN PASSWORD 'REPLACE_WITH_A_LONG_RANDOM_PASSWORD';
CREATE DATABASE snck_panel OWNER snck_panel;
```

Use a strong unique password. If PostgreSQL is on another host, configure its network access and TLS policy separately. Never expose PostgreSQL publicly without firewall restrictions and appropriate authentication/TLS.

## Install the Laravel panel

### 1. Install prerequisites

Install PHP 8.3+ with the required extensions, Composer, Node.js 20+, npm, Git and access to a configured PostgreSQL database. The exact package names can differ between Ubuntu and Debian releases.

Verify:

```bash
php -v
php -m
composer --version
node --version
npm --version
```

### 2. Run the one-command installer

After this branch is merged into `main`, the standard command will install the Laravel application:

```bash
curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/main/install.sh | sudo bash
```

To test this repair branch before it is merged:

```bash
curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/repair/php-laravel-canonical/install.sh | sudo env SNCK_BRANCH=repair/php-laravel-canonical bash
```

The root installer downloads `scripts/install-laravel-panel.sh` from the selected branch and forwards its options. The Laravel installer prompts for PostgreSQL connection details and the public panel URL before running migrations. For unattended installs, provide `SNCK_DB_HOST`, `SNCK_DB_PORT`, `SNCK_DB_DATABASE`, `SNCK_DB_USERNAME`, `SNCK_DB_PASSWORD`, and `SNCK_APP_URL` in the root environment.

Optional installer arguments:

```text
--dry-run
--app-dir=/absolute/path
--repo=https://github.com/SnckBoy/snck-panel.git
--branch=BRANCH_NAME
--help
```

The installer backs up an existing application directory before replacing it. Review the script before running it on a production host.

### 3. Configure the environment

Edit the deployed environment file:

```bash
sudo nano /opt/snck-panel-laravel/.env
```

At minimum, set the production application and database values:

```dotenv
APP_NAME="SNCK Panel"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://panel.example.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=snck_panel
DB_USERNAME=snck_panel
DB_PASSWORD=REPLACE_WITH_THE_DATABASE_PASSWORD
DB_SSLMODE=prefer

SESSION_SECURE_COOKIE=true
```

Use your real hostname and database details. Choose the PostgreSQL SSL mode that matches your database server's certificate configuration; do not weaken TLS verification just to bypass a certificate problem. Keep `.env` private and never commit it.

The installer generates the Laravel application key if needed, installs Composer/npm dependencies, builds frontend assets, runs migrations, and caches configuration/views. It deliberately does not cache routes because the current home route uses a closure. If you change environment values after installation, clear/rebuild Laravel's configuration cache as appropriate:

```bash
cd /opt/snck-panel-laravel
sudo -u snck-panel php artisan config:clear
sudo -u snck-panel php artisan config:cache
```

### 4. Complete first-owner setup

Open your configured panel URL. When no user exists, the application redirects to the first-owner setup page at:

```text
https://panel.example.com/setup
```

Create the initial owner account there. After setup, sign in and configure panel settings before allowing other users to access the service.

## Web server, HTTPS and firewall

The Laravel installer writes an Nginx site listening on HTTP port 80. It does **not** issue TLS certificates or configure Cloudflare Tunnel/Zero Trust. Before exposing the panel to users:

1. Configure a real DNS hostname for the server.
2. Configure HTTPS using a trusted certificate, or place the panel behind a correctly configured Cloudflare Tunnel/reverse proxy.
3. Ensure the proxy forwards the original host and HTTPS scheme correctly.
4. Set `APP_URL` to the public HTTPS URL and `SESSION_SECURE_COOKIE=true`.
5. Restrict SSH and other administrative ports with your firewall. Do not expose PostgreSQL or a node's Docker API to the public internet.

The installer currently writes the Nginx virtual host with `server_name _;` and removes the default Nginx site symlink. Review the generated Nginx configuration before using a host that already serves other websites.

## Services and logs

The installer registers these systemd units:

- `snck-panel-laravel.service` — Laravel queue worker.
- `snck-panel-laravel-scheduler.timer` — runs Laravel's scheduler.
- `snck-panel-discord-worker.timer` — runs Discord maintenance.
- Nginx and PHP 8.3-FPM.

Useful checks:

```bash
sudo systemctl status nginx php8.3-fpm snck-panel-laravel --no-pager
sudo systemctl status snck-panel-laravel-scheduler.timer snck-panel-discord-worker.timer --no-pager
sudo journalctl -u snck-panel-laravel -n 100 --no-pager
sudo journalctl -u nginx -n 100 --no-pager
```

Laravel logs are in:

```text
/opt/snck-panel-laravel/storage/logs/
```

## Node daemon compatibility

The node daemon communicates with the panel using a separate authenticated HTTP API. The checked-in v1 contract is documented in [NODE_DAEMON_API_V1.md](laravel-panel/docs/NODE_DAEMON_API_V1.md).

Important compatibility and deployment requirements:

- Node credentials are per-node bearer credentials; they are not user login tokens.
- Panel-to-node traffic must use HTTPS with normal certificate and hostname verification.
- The PHP client must preserve the frozen v1 methods, paths and payload formats.
- The existing root `node-install.sh` identifies itself as a ShiroNex installer. Do not assume it is a supported installer for every Laravel deployment without verifying the daemon version and API compatibility first.

Do not expose Docker's socket or unauthenticated daemon endpoints to the internet. Use a private network or secure tunnel and firewall rules.

## Development and tests

From the repository root:

```bash
cd laravel-panel
composer install
cp .env.example .env
php artisan key:generate
```

Configure a development PostgreSQL database in `.env`, then run:

```bash
php artisan migrate
npm ci
npm run build
php artisan test
```

The checked-in PHPUnit configuration also defines an in-memory SQLite test environment for tests that support it. Database-specific behavior should additionally be verified against PostgreSQL.

The repository's existing root CI workflow primarily tests the older Node/TypeScript code. Do not interpret a green root CI run as proof that the complete Laravel application, PostgreSQL deployment, installer, or node integration has passed end-to-end testing.

## Current deployment limitations

This branch is still being consolidated. In particular:

- The root `install.sh` is still the legacy React/Express installer; it is not the Laravel installer.
- The Laravel CLI installer requires PHP/Composer/Node/npm to be installed before it starts and requires a working PostgreSQL database.
- The CLI installer creates a dedicated PHP-FPM pool running as `snck-panel` and grants Nginx access to its socket. Only Laravel's `storage/` and `bootstrap/cache/` directories are writable by the application user; do not make the whole application world-writable.
- The installer does not configure HTTPS, DNS, Cloudflare Tunnel, PostgreSQL server provisioning, or node networking.
- Node daemon compatibility and full production integration tests must be completed before treating remote-node deployment as production-ready.

## Security notes

- Keep `.env`, database passwords, node credentials, API keys and OAuth secrets private.
- Use HTTPS for all public panel access and panel-to-node communication.
- Use unique, strong credentials and least-privilege database accounts.
- Restrict access to SSH, PostgreSQL and node management ports.
- Keep backups off-host and test restoration procedures.
- Set `APP_DEBUG=false` in production.
- Review security updates for PHP, Laravel, Composer dependencies, Node.js and the operating system.

## Useful documentation

- [Laravel documentation](https://laravel.com/docs/12.x)
- [Laravel application README](laravel-panel/README.md)
- [Node daemon v1 API contract](laravel-panel/docs/NODE_DAEMON_API_V1.md)
- [Repository issues](https://github.com/SnckBoy/snck-panel/issues)
- [Repository source](https://github.com/SnckBoy/snck-panel)

## License

No repository-level license was verified while preparing this README. Check the repository's license file or obtain the project owner's decision before redistributing the project.
