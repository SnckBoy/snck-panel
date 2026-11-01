# Phase 3 Handoff — Daemon and Server Integration

## Status

Phase 3 is implemented and verified on the `migration/phase2-laravel12-foundation` branch. The frozen daemon v1 route manifest is compared with the actual `node-daemon/src/index.ts` route registrations by an automated test. The Laravel daemon client uses an explicit method/path allowlist, per-node bearer credentials, HTTPS-only endpoints, normal certificate verification, and optional configured CA certificates. It rejects HTTP and missing credentials rather than silently weakening transport security.

The panel now includes node inventory/create/update/health/restart operations, authenticated daemon heartbeat ingestion, server creation with transactionally reserved allocations, server lifecycle/status reconciliation, file operations including streamed binary download, backup operations, explicit per-action server-policy checks, and sub-user management. Heartbeats use observed daemon/Docker state; the panel does not manufacture an online/healthy status. The base controller includes Laravel authorization support. SFTP policy access is restricted to privileged staff and the server owner; delegated sub-users are denied.

## Verification

The full Laravel suite passed against a newly created isolated PostgreSQL test database: **26 tests passed, 158 assertions**. The test run also checks the JSON importer’s bcryptjs `$2a$`/`$2b$` hash normalization and login behavior, the frozen daemon route contract, HTTPS/TLS fail-closed behavior, heartbeat authentication, server operations, sub-user restrictions, and path traversal rejection.

Example local setup for a disposable PostgreSQL database (replace the credentials and database name with values appropriate to your test instance):

```bash
cd laravel-panel
composer install
cp .env.example .env
# Configure a disposable PostgreSQL DB_* connection and APP_KEY in .env.
php artisan key:generate
php artisan migrate --force

# Set the following variables to a separate, disposable test database.
DB_CONNECTION=pgsql \
DB_HOST=127.0.0.1 \
DB_PORT=5432 \
DB_DATABASE=snck_phase3_test \
DB_USERNAME=snck_test_runner \
DB_PASSWORD='replace-with-test-password' \
APP_ENV=testing php artisan test
```

Focused checks:

```bash
APP_ENV=testing DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=5432 \
  DB_DATABASE=snck_phase3_test DB_USERNAME=snck_test_runner \
  DB_PASSWORD='replace-with-test-password' php artisan test --filter=NodeDaemonContractTest

APP_ENV=testing DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=5432 \
  DB_DATABASE=snck_phase3_test DB_USERNAME=snck_test_runner \
  DB_PASSWORD='replace-with-test-password' php artisan test --filter=ServerApiIntegrationTest

composer validate --strict
composer audit
```

The `tests/TestCase.php` CSRF bypass is test-harness-only to avoid simulating browser cookie/token roundtrips in feature tests. It does not alter the production middleware stack. The PostgreSQL database used for tests must be disposable: migrations and test transactions are run against it, never against production data.

## Contract and operational boundaries

`contracts/node-daemon-v1.json` and `docs/NODE_DAEMON_API_V1.md` freeze the route inventory and transport/authentication requirements. Keep the Node daemon deployed and compatible with this v1 contract. The daemon must be provisioned with HTTPS and valid certificates; the panel does not support disabling verification. For private PKI, configure a trusted custom CA.

There is no SFTP operation in the frozen v1 daemon API. This phase closes the SFTP authorization gap by denying sub-user access, but it does not implement remote SFTP credential lifecycle or claim SFTP feature parity in Laravel. Do not expose a new SFTP path until its protocol, credential handling, and access checks have a separately reviewed and versioned contract.

## Not complete / next phase

This package is a staged migration, not a production cutover. Phase 4 remains: full Snck Theme builder/system parity with safe JSON-only ZIP import/export and archive limits. Discord rewards, Cloudflare, Playit, API keys, plugins/modpacks, broader server settings/player/property views, and full administrator configuration still require their planned ports. Phase 5 remains: Ubuntu/Debian production installer, final production README and environment guide, backup/import/cutover/rollback runbook, and old-route compatibility rollout. Phase 6 remains: expanded end-to-end, failure-recovery, and production-readiness tests. Continue after user approval at Phase 4; do not begin it as part of this handoff.

Do not point the legacy Node application at PostgreSQL or dual-write JSON and PostgreSQL. Back up, import, verify, then cut over under the Phase 5 maintenance/rollback procedure. Keep the existing Node daemon as a separate service.
