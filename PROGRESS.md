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

### 1. Plugins, mods, and modpacks manager — completed first batch

The Laravel manager now lists/removes installed JARs, installs compatible Modrinth JARs through the daemon, validates archive paths and size limits, prevents imports while the server is running, and uses the daemon replacement contract with explicit confirmation. The focused suite passes 2 tests / 7 assertions. Modrinth dependency expansion and runtime auto-detection remain follow-up hardening within this item.

### 2. SFTP — credential lifecycle completed

The Laravel API now creates, reads, resets, and deletes one hashed SFTP credential per server, returns plaintext only on create/reset, uses the node hostname and configured SFTP port, audits mutations, and denies delegated sub-users. The focused suite passes 2 tests / 13 assertions. A real SFTP transport daemon remains deployment work because the frozen node-daemon v1 contract does not expose SFTP operations.

### 3. Remaining administrator configuration pages

Complete the remaining settings and administration surfaces with protected validation, audit logging, and browser coverage.

### 4. Production installer

Build the Ubuntu/Debian CLI and web installer for PHP-FPM, Nginx, Composer, queue worker, scheduler, systemd, database configuration, import rehearsal, rollback, and the documented runbook.

### 5. Legacy data import

Make the importer operational and run it against PostgreSQL when available; otherwise complete a careful static/schema review and document the limitation.

### 6. Security hardening and release preparation

Run security review, Pint, Larastan, README/MIGRATION_PLAN updates, final verification, `FINAL_REPORT.md`, and open the PR to `main`.

### 7. Discord remainder, credits permitting

Only after items 1–6: OAuth linking, invite attribution, reward claiming, gateway worker, and retention cleanup.

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

The `php-migration-final` branch contains the Laravel 12 foundation, daemon/server integration, Batch 1 server experience, and Batch 2 API keys/admin settings. The Cloudflare batch now adds encrypted account storage, token verification, staff-only zone/DNS endpoints, SRV-aware validation, audit events, reconnect upserts, bounded pagination, and HTTP-faked feature coverage. Verification completed with the Laravel frontend build, `composer audit` reporting no advisories, and 54 passing tests / 259 assertions; two PostgreSQL-only importer tests are intentionally skipped under the SQLite test configuration.

The Playit batch adds owner/staff-only status, start, stop, and reset endpoints, audit events, fixed-version agent download, server-specific storage, claim-link extraction, bounded PM2 process execution through argument arrays, and tests proving delegated users cannot access the integration. The focused Playit suite passes 2 tests / 9 assertions. Real Playit/PM2 operation still requires a deployment host with PM2 and network access.

The first Discord rewards batch adds encrypted single-document state storage, redacted administrator configuration, strict Discord/OAuth validation, reward-plan CRUD, duplicate protection, audit events, and staff-only endpoints. Its focused suite passes 3 tests / 14 assertions. Live Discord gateway lifecycle, OAuth linking, invite attribution, retention cleanup, member reconciliation, and claim provisioning remain to be ported; this batch does not expose them as completed features.

Remaining ordered work is now SFTP, remaining administration, the production installer, legacy PostgreSQL import verification, release hardening/PR preparation, and only then the remaining Discord worker features if credits remain.
