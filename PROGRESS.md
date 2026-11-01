# Snck migration progress

Updated 2026-10-31 after Stage A2.

## Completed before this stage

The Laravel foundation, first-run Owner setup, authentication, PostgreSQL domain schema, JSON import validation, node heartbeat contract, authenticated HTTPS NodeClient, server policies, server lifecycle/file/backup contracts, sub-user authorization, theme catalog, safe theme import/export, nodes, allocations, server detail, and Phase 4 regression coverage were restored and verified. The baseline before Stage A passed 36 tests and 227 assertions.

## Stage A — completed

The Livewire server detail component now marks `serverId` with `#[Locked]`, preventing client-side Livewire payloads from changing the authorized server target. The browser now has a `/servers/create` page with node, allocation, owner, server type/version, Java, memory, CPU, and disk fields. Stage A2 moved provisioning into the shared `ServerProvisioner` service, so the API and Livewire flow use the same transaction, quota, authorization, allocation, and daemon logic.

Navigation was reduced to destinations that exist in the current migration. Unported global integrations and unported server tabs are no longer offered by the theme layout catalog. The main layout and server list expose only the shipped Dashboard, Servers, Create server, Nodes, and Snck Theme destinations.

Registration is **disabled by default**. It becomes available only when an owner/admin explicitly enables the `enableRegistration` setting. First-run Owner setup remains available when no user exists.

Non-staff server quotas now use safe defaults of 3 servers, 8,192 MB total RAM, 400 CPU percentage points, and 51,200 MB total disk metadata. Owner/admin configuration can override these values through the `quota_max_servers`, `quota_total_ram_mb`, `quota_total_cpu_percent`, and `quota_total_disk_mb` settings. Staff are unrestricted.

## Stage A2 — completed

`ServerProvisioner` is now the single provisioning implementation for the API controller and browser Livewire flow. Non-staff owner quotas are checked inside the same transaction that locks the owner row, node row, and allocation row. A2 regression coverage verifies quota rejection, non-staff cross-owner rejection through API and Livewire, staff creation for another owner, and a mocked-daemon Livewire create-and-redirect flow.

## Ordered remaining work

### B. Server properties, players, settings, and live console/logs

Add the missing browser pages and daemon/API contracts with explicit permissions, live console/log refresh behavior, and regression tests.

### C. Production installer and migration rehearsal

Build the Ubuntu/Debian installer for PHP-FPM, Nginx, Composer, queue worker, scheduler, systemd, database configuration, import rehearsal, rollback, and a documented runbook.

### D. API keys and administrator configuration

Add scoped API keys, rotation/revocation, audit logging, and protected administrator configuration screens.

### E. Discord, Cloudflare, and Playit

Implement each integration behind explicit configuration, secrets handling, health checks, and disabled-by-default behavior.

### F. Plugins, mods, modpacks, and SFTP contract proposal

Add safe marketplace/import flows, runtime detection, archive validation, and document/version the SFTP contract before exposing it.

### G. Real Docker-node test matrix

On a real disposable Docker node, test create, start, stop, files, backup, and a real Minecraft server. Record the results separately from mocked or contract tests.

## Stage test commands

From `laravel-panel/`:

```bash
composer install --no-interaction --prefer-dist
php artisan test
npm ci --no-audit --no-fund
npm run build
php artisan view:cache
```

For Stage A specifically, also verify:

```bash
php artisan route:list --path=servers
php artisan view:cache
```

For A2 specifically, run:

```bash
php artisan test --filter=ServerProvisioningA2Test
```

The quota settings are `quota_max_servers`, `quota_total_ram_mb`, `quota_total_cpu_percent`, and `quota_total_disk_mb`. Their safe defaults are used when the settings are absent or invalid.

## Laravel migration batches

The `php-migration-final` branch contains the Laravel 12 foundation, daemon/server integration, Batch 1 server experience, and Batch 2 API keys/admin settings. The Cloudflare batch now adds encrypted account storage, token verification, staff-only zone/DNS endpoints, validation, audit events, and HTTP-faked feature coverage. Local Laravel verification remains pending on environments without PHP 8.3, Composer, and PostgreSQL; do not treat this batch as release-verified until `composer install`, `php artisan test`, `composer audit`, and the frontend build complete in the project toolchain.

Remaining ordered work includes Playit, Discord rewards, plugins/mods/modpacks, SFTP contract work, remaining administration, the complete importer/installer rehearsal, security hardening, and final production-readiness testing.
