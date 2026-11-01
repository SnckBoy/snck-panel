# SNCK Panel Laravel Migration Plan

Updated 2026-11-01 for `php-migration-final`.

## Objective

Move the panel from the legacy React/Express JSON-backed runtime to Laravel with PostgreSQL persistence while preserving authentication, ownership, server/node behavior, import safety, and operational recovery. This branch is a release candidate; it does not perform a production cutover.

## Completed scope

- Laravel 12 / PHP 8.3 foundation, PostgreSQL schema, authentication, Owner setup, server policies, nodes, allocations, daemon/client contracts, server lifecycle, files, backups, sub-users, themes, and regression coverage.
- Plugin/mod/modpack manager with bounded marketplace downloads, archive/path validation, running-server protection, and daemon replacement contract.
- SFTP credential create/read/reset/delete API with hashed storage, password redaction, audit events, and delegated-user denial. A live SFTP transport remains a separate deployment dependency because it is absent from node-daemon v1.
- Staff-only administrator users API and Livewire page for role/status/password management, owner/self protections, validation, and audit events.
- Production installer with dry-run preflight, Ubuntu/Debian dependencies, Nginx/PHP-FPM, migrations, assets, queue worker, scheduler timer, rollback sibling backup, systemd units, and `/install` web setup alias.
- Legacy JSON importer with dry-run, atomic/idempotent behavior, validation, and PostgreSQL 16 verification. Dedicated importer tests pass 2 tests / 48 assertions; the full PostgreSQL suite passes 67 tests / 361 assertions.
- Release hardening: Pint formatting, Composer audit, Larastan configuration/baseline, security scan, README, final report, and PR preparation.

## Required release gates

Before production use, an operator must configure PostgreSQL, run the installer dry-run, rehearse the importer against a backup, compare counts and relationships, verify node TLS and daemon versions, configure HTTPS/reverse proxying, restore a backup in a separate environment, and review the final pull request. No destructive migration command should run against the legacy database without a verified backup and rollback plan.

From `laravel-panel/`:

```bash
composer install --no-interaction --prefer-dist
php artisan test
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_DATABASE=snck_panel_test DB_USERNAME=snck_panel DB_PASSWORD=secret php artisan test
composer audit
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=1G
npm ci --no-audit --no-fund
npm run build
```

## Deferred scope

Discord OAuth linking, invite attribution, claim provisioning, gateway worker lifecycle, member reconciliation, retention cleanup, and remaining moderation/reconciliation endpoints are deferred until this release candidate is reviewed and accepted.

## Known limitations

The target is Laravel 12, not Laravel 11. The installer requires Node.js 20+ and systemd for service registration. SFTP credentials do not create a transport service. Larastan uses a checked-in baseline for existing migration type findings; new findings must fail analysis. The legacy runtime and JSON data remain the rollback source until a separately approved cutover.
