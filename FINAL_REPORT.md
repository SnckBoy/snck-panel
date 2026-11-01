# SNCK Panel Laravel Migration — Final Report

**Date:** 2026-11-01  
**Branch:** `php-migration-final`  
**Repository:** `SnckBoy/snck-panel`

## Executive result

The Laravel migration branch is a substantial, tested release candidate. The ordered priorities requested for this work were completed through release preparation: plugins/mods/modpacks, SFTP, administrator configuration, production installer, legacy import verification, and security/tooling/documentation hardening. The remaining Discord gateway/OAuth/claim-worker scope was intentionally not started.

This is not a production cutover approval. PostgreSQL, node TLS/daemon compatibility, backup restoration, reverse proxy configuration, and the separate SFTP transport must be validated in the target environment before switching traffic.

## Delivered milestones

- **Plugin/mod/modpack manager:** installed artifact listing/removal, bounded Modrinth download/install, validation, running-server protection, modpack replacement contract, and feature tests.
- **SFTP:** one credential per server, hashed password storage, create/reset/delete lifecycle, password redaction on read, owner/staff policy boundary, audit events, and delegated-user denial. The API exposes connection metadata but does not install an SFTP daemon.
- **Administrator configuration:** staff-only API and Livewire users page; account listing, role/status/password changes, owner/self-protection, validation, and audit events.
- **Production installer:** `scripts/install-laravel-panel.sh` with `--dry-run`, OS/runtime checks, PostgreSQL client, Nginx/PHP-FPM, Laravel migrations, frontend build, queue worker, scheduler timer, rollback sibling backup, and systemd registration. `/install` aliases the guarded first-owner `/setup` flow.
- **Legacy import:** importer behavior was tested on PostgreSQL 16, not only the lightweight local test database.
- **Release hardening:** all 98 PHP files pass Pint; Composer audit reports no advisories; Larastan passes with a baseline containing pre-existing migration type debt; the root README, migration plan, installer runbook, progress log, and this report are current.

## Verification evidence

| Check | Result |
|---|---:|
| Full PostgreSQL Laravel suite | **67 passed / 361 assertions** |
| Legacy importer PostgreSQL tests | **2 passed / 48 assertions** |
| Pint | **98 files pass** |
| Larastan | **No new errors** |
| Composer audit | **No security advisories** |
| Shell syntax for Laravel installer | **Pass** |
| Installer sandbox dry-run | Correctly stopped because sandbox Node.js is below required Node 20+ |

The normal lightweight suite also passed 65 tests / 313 assertions before PostgreSQL-specific verification. PostgreSQL-only coverage removed the two expected skips.

## Security review

The final scan found no use of `eval`, `unserialize`, shell command execution in the Laravel application, or unsafe plaintext credential persistence in the delivered integrations. Sensitive node, Cloudflare, and access credentials remain encrypted or hashed according to their use. File and archive operations continue through validated daemon contracts and bounded paths. SFTP plaintext passwords are returned only on create/reset responses and are never returned by GET.

Residual deployment risks remain: node addresses are administrator-controlled outbound targets and must be protected by network policy; TLS verification and private CA configuration must be reviewed; the installer downloads package/repository content and should be run from a trusted source or pinned release; archive expanded-size and symlink behavior should be rechecked against the exact production runtime; and the Larastan baseline should be reduced as migration type annotations improve.

## Release recommendation

**Recommendation: approve for staging review, not immediate production cutover.** Merge the pull request only after reviewing the changed route/policy surface, validating the installer on the target supported distribution, rehearsing JSON import and restore, confirming the daemon version contract, and deciding how/where the SFTP transport will run. Keep the legacy runtime and backups available until those gates pass.

## Deferred work

Discord OAuth linking, invite attribution, reward claiming, gateway worker, retention cleanup, and related moderation/reconciliation flows remain deferred. These should be implemented as a separate post-release batch rather than being mixed into the migration cutover.
