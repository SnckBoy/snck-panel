# Phase 2 Handoff — Laravel 12 Foundation

## Scope completed

This phase adds a PHP 8.3 / Laravel 12 foundation with Blade and Livewire, PostgreSQL-first schema, first-owner bootstrap, username/email password login, optional Google OAuth, owner/admin/user roles, disabled-account enforcement, deny-by-default server policy, secure one-way JSON import, and an explicit frozen Node Daemon v1 route manifest. The new PHP application does not dual-write to `.data`. The old Node/Express application and its existing routes have not been cut over or removed.

Dashboard and server inventory are read-only in Phase 2. The imported server status is historical data; node rows are intentionally marked offline until the PHP panel makes a real authenticated health request. No health or Docker state is fabricated.

## PostgreSQL local setup

Requires PHP 8.3 with `pdo_pgsql`, `mbstring`, `openssl`, and `zip`, Composer 2, Node.js 22/npm (only for compiled Blade assets), and PostgreSQL 17 or a compatible supported PostgreSQL server.

```bash
cd laravel-panel
cp .env.example .env
# Configure DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD for a disposable PostgreSQL database.
composer install
php artisan key:generate
php artisan migrate --force
npm install
npm run build
APP_ENV=testing php artisan test
php artisan serve --host=127.0.0.1 --port=8000
```

For the supplied local Compose stack, set a unique long `DB_PASSWORD` in `.env`, build/start Postgres and PHP, then migrate explicitly:

```bash
cd laravel-panel
cp .env.example .env
# Edit .env: set APP_URL=http://localhost:8000, DB_PASSWORD=<long-random-value>, and generate APP_KEY below.
composer install
php artisan key:generate
docker compose up --build -d db
docker compose run --rm app php artisan migrate --force
docker compose up --build -d app
```

The Compose app binds to `127.0.0.1:8000` and the database is not published to the host. This is a development/test stack, not the Phase 5 production installer. Behind public HTTPS, set `SESSION_SECURE_COOKIE=true` and configure the production web server/trusted proxy before exposing the app.

Do not run `migrate:fresh` against real data. It drops tables.

## One-way JSON migration

The importer reads only top-level `*.json` from the supplied legacy `.data` directory. It has a dry-run, enforces JSON/size/reference/role checks, refuses insecure remote-node TLS settings, creates a private ZIP backup before any database write, and writes all rows in one PostgreSQL transaction. It preserves every source JSON record encrypted with the Laravel `APP_KEY`; normalized rows are separately stored. It never edits/deletes the source directory. Unknown JSON collections remain in the encrypted `legacy_json_records` table for later feature ports.

If a node/Cloudflare credential is encrypted by the legacy application, the migration operator must temporarily set `LEGACY_NODE_ENCRYPTION_KEY` to the previous Node app `NODE_ENCRYPTION_KEY`. The importer decrypts that value and re-encrypts it under Laravel `APP_KEY`. Do not send either key in chat or commit them. Remove `LEGACY_NODE_ENCRYPTION_KEY` from the target environment immediately after a successful import.

```bash
cd laravel-panel
# Set DB_* and APP_KEY in .env; provide the legacy key only when encrypted node/Cloudflare secrets exist.
php artisan snck:import-json /absolute/path/to/old-panel/.data --dry-run
php artisan snck:import-json /absolute/path/to/old-panel/.data --backup-dir=/absolute/private/path/snck-backups
# Verify a backup ZIP exists, review row counts, then remove LEGACY_NODE_ENCRYPTION_KEY.
php artisan migrate:status
```

The importer requires PostgreSQL (not SQLite) and PHP `ext-zip`. It fails rather than dropping rows when primary/foreign IDs, account roles, or TLS settings cannot be safely mapped. Imported remote nodes start `offline` with `docker_available=false`; the old cached green state is not treated as a live probe. Unrecognized/unsupported password hashes are retained only inside the encrypted source archive and the corresponding account is disabled pending a reset.

### Rollback boundary

Phase 2 does not perform production cutover. Keep the old service and original `.data` untouched and backed up. If an import or Phase 2 test deployment fails, stop the Laravel test service and continue using the original Node/Express service against its original `.data`; the failed PostgreSQL transaction rolls back. To restore imported PostgreSQL later, use the matching `pg_dump` snapshot and restore into a new database; do not point the legacy service at the Laravel database or copy normalized PostgreSQL records back into `.data`. A production cutover/rollback procedure belongs to Phase 5 and must include an operator-confirmed maintenance window.

## Tests and checks

```bash
cd laravel-panel
composer validate --strict
composer audit
APP_ENV=testing php artisan test
APP_ENV=testing php artisan test --filter=NodeDaemonContractTest
```

To run the PostgreSQL-only importer feature tests, set `APP_ENV=testing`, `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE` (a disposable test database), `DB_USERNAME`, and `DB_PASSWORD` in the test environment before running `php artisan test --filter=LegacyImporterTest`. The feature test creates a temporary `.data` fixture, proves dry-run purity, imports and verifies encrypted secrets/backups/resource units/access grants, and checks idempotency.

## Phase 2 files added/changed

- Laravel dependencies, PHP extensions, Compose and Docker build: `composer.json`, `composer.lock`, `Dockerfile`, `docker-compose.yml`, `.dockerignore`, `.env.example`.
- Database/auth foundation: migrations, `User`, `Node`, `Server`, `Allocation`, `Backup`, role/account middleware, server policy, auth controller/provider, Google services config and routes.
- Initial PHP UI: login/setup/register Blade views, dashboard and read-only server inventory Livewire components/views, shared layout and CSS.
- Data migration: `ImportLegacyJson` command, temporary legacy-key config, encrypted import tables, and tests.
- Daemon compatibility: `contracts/node-daemon-v1.json`, `docs/NODE_DAEMON_API_V1.md`, and source-comparison test.
- Tests: auth/roles, server policy, node TLS/auth, contract set, and PostgreSQL import behavior.

## Not done yet

Phase 3: complete server/node/allocations API and management through the daemon's exact frozen v1 contract, with real authentication, confirmed TLS CA verification, file streaming, and safe authorization checks. Phase 4: complete Snck Theme system and bounded safe JSON-only ZIP import/export. Phase 5: Ubuntu/Debian installer, production `.env.example`/README update, controlled backup/import/cutover/rollback runbook, and retaining old routes until daemon rollout verification. Phase 6: broader end-to-end, compatibility, failure-recovery, and security testing for the finished migration.
