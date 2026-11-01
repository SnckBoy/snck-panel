# SNCK Panel Laravel Migration Plan

## Contents

1. [Snapshot and status](#1-snapshot-and-status)
2. [Executive assessment](#2-executive-assessment)
3. [Current route inventory](#3-current-route-inventory)
4. [Product feature inventory](#4-product-feature-inventory)
5. [Data model and persistence](#5-data-model-and-persistence)
6. [Roles and permissions](#6-roles-and-permissions)
7. [Snck Theme parity](#7-snck-theme-parity)
8. [Security findings and validation risks](#8-security-findings-and-validation-risks)
9. [Laravel prototype baseline](#9-laravel-prototype-baseline)
10. [Phased migration and approval gates](#10-phased-migration-and-approval-gates)
11. [Decisions required before Phase 2](#11-decisions-required-before-phase-2)
12. [Phase 1 exit status](#12-phase-1-exit-status)

## 1. Snapshot and status

| Item | Value |
|---|---|
| Repository | `SnckBoy/snck-panel` |
| Snapshot | `main`, commit `580aae915d0c9e5d57d735ad2aa56a382587f577` |
| Checkout state at audit | Detached at requested commit; clean before this document was created |
| Status | **Phase 1 complete: audit reduction and plan only** |
| Implementation | **All implementation phases not started** |

This plan describes the checked-out snapshot, not a claim that anything has been migrated. The user approval gate is mandatory: do not begin Phase 2 or any later phase until the user explicitly approves this plan and resolves the decisions required below. Phase 1 is not authorization to edit application code, schema, installers, tests, or Git metadata.

## 2. Executive assessment

The active product in the repository root is a React/Vite SPA backed by an Express/Node API and JSON-file persistence under `.data`; the node daemon is a separate service. Evidence includes root `package.json`, `server.ts`, `src/App.tsx`, and the JSON-backed route/service code. `laravel-panel/` is a separate prototype/foundation, not the active root application and not a completed replacement. Its UI, route set, models, migrations, and daemon client are materially narrower than the root product.

There is a consequential platform mismatch. The Laravel prototype’s `composer.json` requires Laravel `^12.0` and PHP `^8.2`; its Dockerfile uses PHP 8.3. The user target is **Laravel 11 on PHP 8.3**. Treat this as a target-version change requiring an explicit Composer/dependency compatibility plan, not as a version already satisfied by the prototype.

There is also no single canonical database established in the snapshot: Laravel config defaults to SQLite (`laravel-panel/config/database.php`), its Compose stack provisions MariaDB 11.4 (`laravel-panel/docker-compose.yml`), while the existing `database/schema.sql` and `scripts/migrate-json-to-postgres.ts` are PostgreSQL-specific. Do not silently select or mix these. **Recommendation:** PostgreSQL is the shortest path to reuse/align with the existing PostgreSQL schema and importer, but those assets are incomplete and require redesign, validation, and tests. If MySQL/MariaDB is preferred, build and test a distinct schema/import path for it; do not assume PostgreSQL DDL/import behavior ports unchanged.

A safe migration is a staged replacement, preserving externally observable behavior and data first, rather than a parallel rewrite of every feature. The highest-risk areas are identity/password compatibility, authorization and ownership, database completeness and idempotent import, panel-to-daemon protocol compatibility, file/archive handling, node credential/TLS topology, and theme import/export. The route and feature inventories below are the parity checklist.

## 3. Current route inventory

Route evidence is primarily `src/App.tsx:34-92` (SPA route guards and route declarations), `src/server/routes/api.ts` (Express mount composition), and the corresponding route modules under `src/server/routes/`. Unless stated otherwise, API routes below are rooted at `/api`. Inventory follows the audit of the current snapshot; verify exact middleware/rate-limit placement during implementation rather than inferring it solely from a path list.

### 3.1 SPA routes

| Route | Current page / access note |
|---|---|
| `/setup` | First-run setup |
| `/login`, `/register` | Authentication pages; public-auth guard redirects an already authenticated user |
| `/core-checkpoint` | Core checkpoint page |
| `/` | Dashboard; authenticated UX route |
| `/nodes` | Nodes; authenticated UX route |
| `/servers` | Server list |
| `/servers/create` | Server creation |
| `/servers/:id` | Server detail; nested tabs listed below |
| `/servers/:id/{overview,console,players,properties,files,SFTP,subusers,settings,backups,plugins,mods,modpacks}` | Server views/features; `Playit` view is conditional on applicability/configuration |
| `/allocations` | Admin-only UX wrapper |
| `/cloudflare` | Admin-only UX wrapper |
| `/api-keys` | Admin-only UX wrapper |
| `/admin`, `/admin/servers` | Admin-only UX wrappers |
| `/settings` | Authenticated settings |
| `/free-service/discord` | Discord rewards page, authenticated UX wrapper |
| `/snck-theme` | Admin-only UX wrapper |

`ProtectedRoute` and `AdminOnlyRoute` in the SPA are user-experience navigation guards, **not authorization boundaries**. Every operation must be authorized by the server independently; browser route hiding does not protect API endpoints.

### 3.2 Express API inventory by family

Methods and paths in this table reflect the required audited endpoint inventory. `:id` is the applicable resource identifier; route modules may use different local router prefixes. User-facing endpoints generally use session/JWT auth unless explicitly public; administrative endpoints require staff/admin checks as implemented in the root API. Confirm each route’s exact middleware/limiter in a phase-specific route review.

| Family / purpose | Endpoint inventory | Access distinction |
|---|---|---|
| **Authentication and account** — first-run, registration, login, identity, credentials, user listing | `POST /api/auth/setup`, `/register`, `/login`, `/google`, `/logout`; `GET /api/auth/setup-status`, `/me`, `/users`; `PUT /api/auth/password`, `/username` | Setup/register/login/Google are entry flows with setup/config-dependent behavior; `/me` is authenticated; user listing and account administration must be staff-restricted as applicable. Login/setup abuse throttling is relevant. |
| **Public/core settings and marketplace** | `GET /api/settings`; mounted `GET /api/marketplace/search` | Settings/search are public-facing surfaces in the audited design; return only intended public fields and confirm data minimization. The separately declared `routes/marketplace.ts` is **unmounted/duplicate**, not a second active mounted surface. |
| **Server core/lifecycle** — list/create/read, operational status, control, assignment/settings | `GET/POST /api/servers`; `GET /api/servers/:id`, `/:id/stats`, `/:id/logs`; `DELETE /api/servers/:id`; `PUT /api/servers/:id/owner`, `/:id/ipalias`, `/:id/version`, `/:id/java-version`, `/:id/resources`, `/:id/suspend`; `POST /api/servers/:id/start`, `/:id/stop`, `/:id/kill`, `/:id/restart`, `/:id/command` | Server access is owner or permitted sub-user; staff can administer platform/server operations under role rules. Mutations and daemon forwarding require server-level checks, not only UI checks. |
| **Server files, plugins, modpacks** — browsing, transfer, archive and content management | `GET /api/servers/:id/files`, `/:id/files/download`, `/:id/plugins/installed`; `POST /api/servers/:id/files/upload`, `/:id/files/upload-chunk`, `/:id/modpacks/import`, `/:id/modpacks/install`, `/:id/files/rename`, `/:id/files/save`, `/:id/files/create`, `/:id/files/mkdir`, `/:id/files/unzip`, `/:id/files/zip`; `DELETE /api/servers/:id/plugins/:filename`, `/:id/files` | Owner or relevant `files`, `plugins`, or `mods` permission; upload parser ordering and archive boundaries require particular care (Security section). |
| **Backups** — server backup lifecycle | `GET/POST /api/servers/:id/backups`; `GET/DELETE /api/servers/:id/backups/:filename` | Owner or `backup` permission; validate ownership and filename/path mapping. |
| **Playit** — tunnel lifecycle | `GET /api/servers/:id/playit`; `POST /api/servers/:id/playit/start`, `/:id/playit/stop`, `/:id/playit/reset` | Server owner/staff and applicable server permissions/configuration; exposure is conditional. |
| **Subusers** — delegated server access | `GET/POST /api/servers/:id/subusers`; `DELETE /api/servers/:id/subusers/:userId` | Server owner/staff; sub-user cannot grant authority beyond allowed permission labels. |
| **SFTP** — credentials and account lifecycle | `GET /api/servers/:id/sftp`; `POST /api/servers/:id/sftp/create`, `/:id/sftp/reset-password`; `DELETE /api/servers/:id/sftp` | Owner/staff boundary should apply. Current SFTP sub-user access gap is a confirmed finding; do not carry it over. |
| **System administration** — global configuration, versions, users and update | `GET /api/system/settings`, `/versions`, `/java-versions`, `/paper-versions`, `/stats`, `/users`; `POST /api/system/users`; `DELETE /api/system/users/:id`; `PUT /api/system/users/:id/password`, `/:id/role`, `/:id/settings`; `POST /api/system/update` | Administrative; role changes/free-service control are owner-only where specified. User-facing read access must not be widened accidentally. |
| **API keys** — token lifecycle | `GET/POST /api/admin/api-keys`; `DELETE /api/admin/api-keys/:id` | Staff/admin, with owner/staff constraints verified on each action; API-key authentication is a separate credential mode and requires explicit scope semantics. |
| **Nodes** — node inventory and remote operations | Public `GET /api/nodes/public`; authenticated admin `GET/POST /api/nodes`, `POST /api/nodes/local`, `GET /api/nodes/:id`, `POST /api/nodes/:id/rotate`, `/enable`, `/disable`, `/maintenance`, `/test`, `/reconnect`, `/restart`; `PATCH /api/nodes/:id`; `DELETE /api/nodes/:id`, `/api/nodes/:id/maintenance`; `GET /api/nodes/:id/stats`, `/:id/health` | Public endpoint is intentionally limited; operational inventory/config/credential actions are admin. Public response minimization must be checked. |
| **Node-agent panel callbacks** — local bootstrap, enrollment and liveness | `POST /api/node-agent/local-bootstrap`, `/register`, `/heartbeat` | Daemon credential/bearer trust, not browser-user auth. Keep this trust domain separate from panel role authorization. |
| **Allocations** — IP/port pool and server assignment | `GET /api/allocations/available`, `/api/allocations`; `POST /api/allocations`, `/:id/primary`, `/:id/assign`, `/:id/unassign`; `DELETE /api/allocations/:id` | Admin allocation management; assignment must be transactional and enforce uniqueness/capacity. |
| **Cloudflare DNS** — linked account, zones, records | `GET /api/cloudflare/connection`, `/zones`, `/zones/:zoneId`, `/zones/:zoneId/dns`; `POST /api/cloudflare/connection`, `/zones/:zoneId/dns`; `PUT/DELETE /api/cloudflare/zones/:zoneId/dns/:recordId` | Staff/admin for integration/records; secrets must be protected; outbound DNS-provider behavior needs validation. |
| **Themes** — declarative theme library and application | `GET/POST /api/themes`; `PUT /api/themes/:id`; `PATCH /api/themes/:id/enabled`; `POST /api/themes/:id/apply`, `/api/themes/restore-default`, `/api/themes/import`; `DELETE /api/themes/:id`; `GET /api/themes/:id/export` | Read/apply behavior may serve user preferences; creation/import/enable/delete/restore controls are administrative. Preserve actual route policy when designing parity. |
| **Discord rewards** — OAuth link, reward claims, moderation/configuration | `GET /api/discord-rewards/oauth/callback`, `/me`, `/oauth/start`, `/claim-options`, `/admin`; `DELETE /api/discord-rewards/account/link`; `POST /api/discord-rewards/claims`; `POST /api/discord-rewards/admin/prune`, `/test-connection`, `/sync`, `/plans`, `/events/:id/review`, `/claims/:id/reconcile`; `PUT /api/discord-rewards/admin/config`, `/plans/:id`; `DELETE /api/discord-rewards/admin/plans/:id` | Account link/status/claims are user-scoped; admin configuration, sync, review, reconcile and plan management are admin. OAuth and claims/admin mutation limits are relevant; verify exact limiter placement. |

### 3.3 Node daemon routes (separate service)

The daemon has its own HTTP API under `/v1`, protected by the node bearer credential; it is not the panel’s `/api` namespace. Inventory and contract details are in `node-daemon/src/index.ts` (including `/v1/health`, server lifecycle/console/logs/stats/files operations and heartbeat sender). The daemon also calls back to the panel’s `/api/node-agent/heartbeat`. A migration must preserve/upgrade both sides as a versioned contract, not treat panel route parity as daemon compatibility. Exact complete daemon route list should be frozen from `node-daemon/src/index.ts` before Phase 3 implementation.

## 4. Product feature inventory

The root application currently includes: first-run account setup; password and Google authentication; dashboard; Minecraft server creation and lifecycle; console, commands and live stats; file browsing, upload/download, archive operations; SFTP; backups; player/property/plugin/mod/modpack tools; delegated subusers; node inventory, health and heartbeats; allocation management; Cloudflare DNS; Playit tunnels; API keys; admin user and system configuration; Discord rewards; Snck Theme; update/install/backup workflows; and the separate node daemon. Relevant UI evidence is in `src/pages/`, `src/components/`, `src/App.tsx`, root `server.ts`, and `node-daemon/`.

The Laravel prototype does not yet represent the full root product. It has a small login/setup/dashboard foundation and partial node/allocation/server/file/backup APIs; it does not establish parity for the complete auth flows, Google, server subviews and operations, subusers/SFTP, plugins/modpacks/players/properties, Playit, Cloudflare, API keys, Discord rewards, themes, full system administration, update/install/backup workflows, or daemon enrollment/heartbeat. These are **missing/incomplete in `laravel-panel/`**, not absent from the root application. Do not infer product feature deletion from prototype omissions.

## 5. Data model and persistence

### 5.1 Active JSON persistence

The active root implementation persists JSON documents beneath `.data`, including:

| JSON collection | Domain and migration concern |
|---|---|
| `.data/users.json` | Identity, role, auth providers, credentials and account state |
| `.data/nodes.json` | Node endpoints, credentials/TLS, health, capacity and configuration |
| `.data/servers.json` | Server runtime/configuration, owner/node links, limits and aliases |
| `.data/allocations.json` | Node/IP/port pool and optional server assignment/primary state |
| `.data/settings.json` | Global settings and theme selection/config |
| `.data/api_keys.json` | API-key hashes, metadata, enabled state/scopes |
| `.data/audit_logs.json` | Actor/action/resource/time history |
| `.data/sftp_users.json` | SFTP identity/credentials and server links |
| `.data/cloudflare_accounts.json` | Provider connection and sensitive credentials |
| `.data/themes.json` | Theme declarative data, enabled state and built-ins/custom themes |
| `.data/discord_rewards.json` | Discord configuration, links, plans, claims/events and sync state |

Additionally, runtime server directories, backup artifacts and temporary/upload files are persisted on disk. Their exact layout depends on installation/runtime settings; inventory before cutover and preserve file ownership, permissions, and backups. Root route and service files plus `.gitignore`/installer configuration describe the storage mechanism; this plan does not migrate or inspect live user data.

Relationships include users owning servers; servers assigned to nodes and allocations; subusers attached to servers with permission labels; backups and SFTP identities attached to servers; API key/audit actor links to users; themes referenced by global/user settings; and Discord identities/claims/plans/events linked to users and integration configuration. Legacy shapes have compatibility aliases, notably server `owner` vs `ownerId` and server allocation representations. Import must normalize aliases deterministically, report every rejected/skipped row, and retain stable IDs.

### 5.2 Existing PostgreSQL schema/import limitations

`database/schema.sql` defines PostgreSQL tables, including users, sessions, nodes, node credentials, servers, allocations, subusers, backups, API keys, audit logs and other partial domains (table anchors at lines 1-127). `scripts/migrate-json-to-postgres.ts` reads only users, nodes, servers and allocations (notably lines 60-72). It is not a complete application-data migration. High-impact missing or incomplete coverage includes:

- User `passwordHash`/hash compatibility, Google ID, password version and other auth/provider fields; fallback to `!MIGRATION_REQUIRES_PASSWORD_RESET` may force reset.
- Node credentials, TLS configuration, Cloudflare integration linkage and detailed health/capacity fields.
- Server field normalization, particularly `owner` versus `ownerId`, allocation relationships, runtime/configuration and other fields not included in its INSERT projection.
- Entire JSON collections for API keys, audit logs, SFTP users, Cloudflare accounts, themes, rewards and settings, plus runtime/backups/temp files.
- Several schema tables exist without corresponding importer coverage; schema presence is not evidence of migrated data.

The importer skips server rows without `ownerId` or `nodeId` and uses `ON CONFLICT ... DO NOTHING` in insert paths, so rows may be silently skipped or left unchanged on rerun. Its allocation conflict path updates server assignment. It currently has no migration test proving counts, relationships, secrets handling, idempotency, rollback, or equivalence. This is verified from importer behavior; actual row loss depends on real data and requires a dry-run audit.

### 5.3 Laravel prototype persistence

Laravel migrations currently define framework tables plus users, nodes, servers, allocations, backups, settings and audit events; the prototype has five domain Eloquent models: `User`, `Node`, `Server`, `Allocation`, `Backup`. See `laravel-panel/database/migrations/2026_09_04_000100_create_snck_domain_tables.php`, `laravel-panel/app/Models/`, and `laravel-panel/app/Console/Commands/ImportLegacyJson.php`. It does not model/import the full JSON domains above: API keys, SFTP users, Cloudflare, themes, Discord rewards, and complete settings/audit semantics are absent or incomplete. User/node/server fields and relationships also need a field-by-field parity review. Framework migrations (cache/jobs) are implementation support, not proof of product-domain parity.

## 6. Roles and permissions

The active role vocabulary is `owner`, `admin`, `user`. Staff means `admin` or `owner` for platform administration. Role changes and Free Service controls are owner-only. Users have owned servers and may be added as server subusers; server-owner and delegated membership are distinct authorization paths.

Subuser permission labels are `start`, `stop`, `restart`, `kill`, `plugins`, `mods`, `backup`, `files`, `properties`, `settings`, and `*`. Each route must map to a least-privilege action; `*` must have explicitly defined semantics and must not be silently broadened. Nodes, allocations, theme administration, Cloudflare, API-key administration and system operations are admin/staff domains; owner-only operations remain owner-only. The node daemon’s bearer token is a separate machine identity and does not inherit panel-user roles.

Frontend guards in `src/App.tsx` provide UX only. The Laravel prototype currently relies mostly on inline role checks and does not yet supply a mature granular policy framework or consistent tenant/server ownership model. Phase 2 must establish policies/middleware and server ownership rules before feature expansion; Phase 3 must enforce them on every panel and daemon operation. Do not treat a shared `auth` middleware group as equivalent to authorization.

## 7. Snck Theme parity

Snck Theme is a declarative design system, not merely a color picker. Theme data includes palette tokens; typography, density, radius, opacity and elevation; and structures for layout, navigation, dashboard and server views. Validators enforce semantics and backward normalization supports older manifests. There are seven built-in palettes, plus custom create/edit/clone/apply/enable/delete/restore-default operations. Themes live in `themes.json`; global selection is referenced through `settings.snckThemeId`. ZIP import/export contains JSON only, not executable assets.

Import/export safety controls include a 2 MiB archive limit, a single in-memory file, a 100-entry cap, a 32 KiB manifest bound, and traversal checks. These controls and validator coverage are useful baseline behavior to preserve; tests exercise validators, but the audit reports no archive route tests. Verify actual archive-library behavior, expanded-size handling and symlink semantics at runtime before relying on these controls. Laravel theme parity must include the service, UI and validation/normalization rules, compatibility behavior, per-role actions, and secure declarative ZIP paths. Export/import safety and route-level tests are explicit parity work, not optional polish. Source anchors include `src/components/ThemeLayoutEditor.tsx`, theme service/route files under `src/server/`, `src/pages/` and `scripts/test-themes.mts`.

## 8. Security findings and validation risks

The audit distinguishes verified code-level findings from runtime/environment questions. Confirmed behavior is not automatically proof of an exploitable deployed installation; risks below require contextual validation and must not be represented as confirmed exploits.

### 8.1 Confirmed findings in the audited source

1. **SFTP subuser access gap.** Server SFTP routes in `src/server/routes/servers.ts:283-342` do not enforce an SFTP-specific subuser permission; the permission map lacks SFTP. A subuser with access through that path may reach credential operations beyond intended grants. Close explicitly in policy tests before carrying behavior forward.
2. **Upload parser limits/order.** `src/server/routes/servers.ts:9,81-87` configures upload parsing without the audited multer size limits, and authorization executes after the upload parser. This permits the parser to consume/store upload data before the route rejects an unauthorized request and lacks the intended explicit bounds. Reorder authorization and impose byte/file/count limits, cleanup, and storage quotas.
3. **API key scopes are coarse.** `src/server/middleware/auth.ts:7-14,60-65,152-157` represents generic access flags, not least-privilege per-domain/action scopes. Restrict and document scopes; migrate keys without accidentally granting broader access.
4. **Socket privilege revocation is delayed.** Existing socket authorization can retain stale access after role/membership changes until the relevant connection/session is refreshed or disconnected. Revalidate/revoke on authorization changes and test.
5. **Browser localStorage bearer exposure.** The SPA stores bearer JWT material in `localStorage` (`src/context/AuthContext.tsx`); any successful same-origin script injection could access it. This is an architectural exposure, not evidence that a current XSS exploit exists. Evaluate cookie/session/CSRF alternatives and harden CSP/output paths.

### 8.2 Risks requiring runtime or deployment confirmation

- Public settings, mounted marketplace search and public-node endpoints are intentional surfaces in the current design; verify response-field minimization and rate limiting. Public availability by itself is not classified as a vulnerability.
- Root panel service access to the Docker group has host-impact implications; confirm actual service account/group and deployment isolation.
- Node daemon runs privileged, uses the Docker socket/shared bearer token, and binds `0.0.0.0` in the audited configuration; verify production bind/firewall/network and token rotation/permissions. No exploit is asserted.
- TLS is optional and HTTP may be allowed for node/panel connectivity; verify production topology, cert validation behavior and whether private-network exceptions are intentionally required.
- Installer scripts fetch remote content without immutable pinning; verify URLs/checksums and supply-chain controls. Do not infer compromise.
- Archive expanded-size and symlink handling require runtime confirmation; limits on compressed input/entry count do not alone prove safety against every library/platform behavior.
- Validate current rate limiting and sensitive-field exposure for auth, Discord, API keys, setup and public endpoints against actual route middleware and deployment settings.

### 8.3 Laravel prototype-specific risks / gaps

`laravel-panel/` is prototype code and has no demonstrated production readiness. Its current patterns include no comprehensive API role/ownership policies; setup flow race/no throttling; admin-controlled node addresses creating an SSRF surface; optional TLS verification; raw exception leakage; file paths delegated to daemon behavior; and debug/development configuration concerns. These are code-level design gaps requiring remediation and tests; deployed reachability/exploitability is not established here. Do not expose the prototype as a replacement service until reviewed and gated.

## 9. Laravel prototype baseline

| Area | Current snapshot and implication |
|---|---|
| Framework/runtime | `laravel-panel/composer.json` requires Laravel `^12.0` and PHP `^8.2`; Dockerfile uses PHP 8.3. User target is Laravel 11/PHP 8.3, so dependency/lockfile/runtime changes are Phase 2 scope after approval. |
| Database | `laravel-panel/config/database.php` defaults to SQLite; `laravel-panel/docker-compose.yml` provisions MariaDB 11.4; root PostgreSQL schema/importer are a third, distinct path. No canonical choice is established. |
| Web routes/UI | `laravel-panel/routes/web.php` includes setup, login, logout and authenticated dashboard. UI is a login/setup/dashboard foundation, not the React product’s full user experience. |
| APIs | `laravel-panel/routes/api.php` has authenticated nodes, allocations and partial server/file/backup operations. It is not route/feature parity with root. |
| Domain model | Five Eloquent models (User, Node, Server, Allocation, Backup); domain migrations plus framework support tables. |
| Daemon client | `laravel-panel/app/Services/NodeClient.php` is not protocol-compatible with the daemon: PHP sends `POST /v1/health`, while daemon exposes `GET /v1/health`; PHP create omits port/RAM fields; file list/read methods do not match daemon HTTP methods; daemon restart path differs; JSON client does not support binary downloads; no heartbeat/bootstrap integration. Compare `NodeClient.php`, `node-daemon/src/index.ts`, and the PHP controllers. |
| Tests | Example tests are weak foundation tests, not a cross-product parity, migration, daemon-contract, or security matrix. No successful end-to-end replacement evidence exists. |

## 10. Phased migration and approval gates

The sequence below is fixed: **complete each phase and obtain review/approval before starting the next.** This document is Phase 1 only. No work in Phases 2–6 is authorized yet.

| Phase | Scope, prerequisites and deliverables | Acceptance / stop gate |
|---|---|---|
| **1 — Audit and plan (complete)** | Read-only snapshot reduction; route/feature/data/security baseline; decisions and staged proposal in this file. | This plan is the sole Phase 1 deliverable. **Stop: wait for explicit user approval before Phase 2.** |
| **2 — Laravel 11/PHP 8.3 and data foundation** | Prerequisite: explicit approval and decisions on database, UI strategy, JSON fallback and compatibility window. Set Laravel 11/PHP 8.3 baseline; choose one canonical DB; build complete schema/migrations for product domains; implement a tested legacy importer with dry-run, backup, stable ID/alias mapping, errors/skips report, rerun/idempotency and rollback; establish authentication, password/provider compatibility, roles, granular policies and ownership. | Clean install on chosen DB; supported PHP/runtime verified; fixture and anonymized representative imports match counts, relationships and security expectations; secrets handled safely; rollback demonstrated; policy tests prove owner/admin/user and tenant boundaries. Stop for user review before Phase 3. |
| **3 — NodeClient and server/daemon parity** | Prerequisite: approved Phase 2 schema/auth/policies and contract freeze. Implement compatible HTTPS NodeClient; node registration/bootstrap/heartbeat; explicit cert verification policy; transactional allocation operations; full server lifecycle and daemon-backed APIs (files, binary downloads, logs/stats, backups and supported feature operations); map every root endpoint/permission to migrated behavior; retries/timeouts/idempotency and failure rollback. | Contract tests against supported daemon version(s); lifecycle/file/archive/backup and allocation integration tests; authorization on every action; TLS tests; error/retry/rollback evidence; route parity checklist signed off. Stop for review before Phase 4. |
| **4 — Theme service/UI and safe archive parity** | Prerequisite: approved schema and stable API/policy layer. Implement declarative theme persistence, seven built-ins, normalization/validation and custom create/edit/clone/apply/enable/delete/restore flows; UI parity; JSON-only ZIP import/export with resource, traversal, symlink and expanded-size controls. | Existing theme fixtures round-trip compatibly; invalid/hostile archives rejected; route-level and validator tests; visual/user-role parity accepted. Stop for review before Phase 5. |
| **5 — Ubuntu/Debian install, configuration and operational docs** | Prerequisite: approved application behavior and stable release artifact. Provide/adjust installer for chosen production stack, `.env.example`, README/deployment guidance, backups, service account/permissions, TLS/network setup, upgrade and safe rollback. Installer changes only occur in this phase. | Fresh supported distro install and upgrade/rollback rehearsals; secrets and permissions reviewed; backup restore demonstrated; operational instructions reproducible. Stop for review before Phase 6. |
| **6 — Test matrix, CI and security validation** | Prerequisite: approved prior phase deliverables and deployment candidate. Build CI and full test matrix across auth/roles, imports, API parity, daemon contracts, themes, upgrades, failure recovery and deployment; perform security review and runtime validation of flagged risks. | CI green on supported matrix; unresolved high-severity issues documented/blocked; migration/deployment rehearsals pass; user reviews final readiness before any cutover. |

**Global stop gate:** No phase after Phase 1 begins until the user explicitly says to proceed. Later phases each require completion evidence and explicit review/approval before the next phase. No cutover or destructive data action is implied by this plan.

## 11. Decisions required before Phase 2

1. **Canonical database:** Recommend PostgreSQL because the repository already contains PostgreSQL DDL/import code, while emphasizing that both need completeness and testing. Alternatively select MySQL/MariaDB and authorize a separate tested schema/import path. SQLite should not silently become the production decision.
2. **UI direction:** Keep the current React UI and progressively point it at Laravel APIs, or rebuild progressively in Blade. Recommendation: retain React initially to reduce simultaneous UI and backend change, subject to user preference.
3. **JSON fallback during cutover:** Decide whether to maintain read-only/temporary JSON fallback during rollout. Recommendation: retain a bounded, explicitly temporary rollback path only if concurrency and dual-write consistency can be proven; otherwise make a controlled backup-and-cutover with documented rollback rather than unsafe dual writes.
4. **Legacy API compatibility period:** Define whether `/api` consumers and the node daemon require a compatibility window, versioned endpoints, or coordinated cutover. Recommendation: version/freeze the daemon contract and keep old panel routes until clients are transitioned and observed.
5. **Authorization policy:** Confirm intended owner/admin/user capabilities, server-owner and subuser precedence, `*` semantics, and which settings/marketplace/public-node endpoints are deliberately public. Recommendation: keep public payloads minimal, deny by default, and encode policies server-side.
6. **Archive safety limits:** Approve maximum compressed and expanded bytes, entry count, nesting depth, allowed file types, symlink treatment, processing timeout and temporary-storage cleanup. Recommendation: strict allowlist and expanded-size ceiling, reject symlinks and traversal.
7. **Node private HTTPS topology:** Clarify whether nodes have publicly trusted TLS, private CA, or an isolated private network. Recommendation: TLS with verification by default; explicit managed CA support where needed, never blanket verification disablement.

No Phase 2 work should begin while these choices remain implicit. Any exact deployment-specific assumption not represented here must be verified with the user or against an authorized environment.

## 12. Phase 1 exit status

**Phase 1 plan created. Audit only.** No application code, Laravel code, schema, install script, tests or Git metadata were changed; no data migration or runtime test was run; no commit, push or PR was created. The only intended artifact is `MIGRATION_PLAN.md`. **Next action: wait for explicit user approval and the Phase 2 decisions above.**