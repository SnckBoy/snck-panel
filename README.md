# SNCK Panel

SNCK Panel is a self-hosted Minecraft server control panel. This branch, `php-migration-final`, contains the Laravel migration release candidate under `laravel-panel/`; the legacy React/Express application remains available for controlled rollback and comparison.

## Current status

Completed on this branch: Laravel authentication and PostgreSQL schema; server lifecycle, files, backups, sub-users, node heartbeat/client contracts, themes, Cloudflare DNS, Playit, plugin/mod/modpack management, SFTP credential lifecycle, administrator configuration, production installer, and legacy JSON import. Discord gateway/OAuth/claim-worker features are intentionally deferred until after release review.

The migration targets Laravel 12 and PHP 8.3. PostgreSQL is the supported production database. The SFTP API stores and rotates credentials, but a live SFTP transport daemon is not part of the frozen node-daemon v1 contract and must be deployed separately before advertising file-transfer connectivity.

## Development and verification

```bash
cd laravel-panel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm ci && npm run build
php artisan test
composer audit
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=1G
```

The supported database path can be verified with PostgreSQL:

```bash
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=5432 \
DB_DATABASE=snck_panel_test DB_USERNAME=snck_panel DB_PASSWORD='secret' \
php artisan test
```

Final PostgreSQL verification passed **67 tests / 361 assertions**; Pint passes all 98 formatted files; Larastan passes with a checked-in baseline for pre-existing migration type debt, so new findings fail analysis.

## Production installation

Run a non-destructive preflight first:

```bash
sudo bash scripts/install-laravel-panel.sh --dry-run
```

The installer supports Ubuntu/Debian hosts with PHP 8.3, Node.js 20+, Composer, PostgreSQL connectivity, and systemd. It provisions Nginx, PHP-FPM, migrations, compiled assets, the queue worker, and scheduler. See [`docs/LARAVEL_INSTALLER.md`](docs/LARAVEL_INSTALLER.md). The first-run web installer is available at `/install` (also `/setup`) and creates the first Owner exactly once.

## Migration policy

No destructive cutover is performed by this repository work. Back up the existing application and database, rehearse the JSON importer with `--dry-run`, compare counts and relationships, and keep the current deployment available until environment-specific review is complete. See [`MIGRATION_PLAN.md`](MIGRATION_PLAN.md), [`PROGRESS.md`](PROGRESS.md), and [`FINAL_REPORT.md`](FINAL_REPORT.md).

Use HTTPS, a restrictive firewall, private `.env` permissions, rotated credentials, and a separate backup location in production. Do not expose the Docker socket or node daemon to untrusted networks.
