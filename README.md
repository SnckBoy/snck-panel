# SNCK Panel

SNCK Panel is a self-hosted Minecraft server management panel. The **Laravel application in `laravel-panel/` is the PHP implementation and the intended primary panel** on this branch.

> **Branch status:** This documentation is for `main`. The repository's root `install.sh` still belongs to the older React/Express installer path. It does **not** install the Laravel panel. Do not use the root one-command installer when you intend to deploy the PHP application.

## Project layout

| Path | Purpose |
| --- | --- |
| `laravel-panel/` | Laravel 12 application, Blade/Livewire UI, API routes, migrations and tests |
| `scripts/install-laravel-panel.sh` | CLI installer for the Laravel application |
| `scripts/` | Legacy/native panel scripts and utilities |
| `node-install.sh` | Laravel-compatible node installer; downloads the daemon source, provisions trusted TLS and an SSH host key, and configures the node service |
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
- Per-server subuser management, SFTP credentials, and an SSH/SFTP transport jailed to each server's files.
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

The installer checks for PHP, Composer, Node.js and npm before it installs packages. Install those prerequisites first. It installs the PostgreSQL client, but **does not install or provision a PostgreSQL server/database**.

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

### 2. Download and run the Laravel installer from this branch

The installer must be fetched from the same branch you intend to deploy. The following command installs this branch into the default directory, `/opt/snck-panel-laravel`:

```bash
curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/main/scripts/install-laravel-panel.sh -o /tmp/snck-panel-installer.sh
sudo bash /tmp/snck-panel-installer.sh --branch=main
```

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

The installer generates the Laravel application key if needed, installs Composer/npm dependencies, builds frontend assets and runs migrations. If you change environment values after installation, clear/rebuild Laravel's configuration cache as appropriate:

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
- The Infrastructure page creates a one-time install command using the node's bearer credential. The installer downloads the daemon from the repository's `main` branch; after merging this PR, the installer and panel API contract are aligned.
- The node FQDN must resolve to the node VPS. The installer obtains a trusted certificate with Let's Encrypt HTTP-01 on TCP port 80, then starts the daemon over HTTPS. It also creates an SSH host key for SFTP.

The node installer configures the SSH/SFTP listener on TCP port 2022 and generates a stable host key. The panel validates each SFTP password through the node-authenticated `/api/node-agent/sftp-auth` endpoint; each login is jailed to that server's directory. Restrict SFTP to trusted networks. Open inbound TCP 80 for certificate issuance/renewal, 6768 for the HTTPS daemon, and 2022 for SFTP.

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

The repository CI workflow now runs the Laravel test suite against PostgreSQL, PHP formatting and static analysis, the Laravel frontend build, the node-daemon build, and SSH/SFTP protocol integration tests. A green CI run still does not replace a live node provisioning, heartbeat, file-transfer, firewall, and certificate-renewal test.

## Current deployment limitations

This branch is still being consolidated. In particular:

- The root `install.sh` is still the legacy React/Express installer; it is not the Laravel installer.
- The Laravel CLI installer requires PHP/Composer/Node/npm to be installed before it starts and requires a working PostgreSQL database.
- The CLI installer currently assigns the application tree to the `snck-panel` user, while the default PHP-FPM pool may run as `www-data`. Verify and correct PHP-FPM write access for `storage/` and `bootstrap/cache/` before production use; do not solve this by making the whole application world-writable.
- The installer does not configure HTTPS, DNS, Cloudflare Tunnel, PostgreSQL server provisioning, or node networking.
- Live node provisioning, heartbeat, Docker lifecycle, SFTP file transfer, firewall behavior, and certificate renewal must be verified in the target environment before treating remote-node deployment as production-ready.

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
