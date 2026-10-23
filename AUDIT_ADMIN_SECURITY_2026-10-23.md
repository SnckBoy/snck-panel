# Admin, security, and operations audit — 2026-10-23

## Scope and confidence

This is a source-level audit of the current GitHub default-branch files reachable through the connected GitHub integration. The working copy could not be cloned in the execution environment because DNS/network access to GitHub failed. No local build, runtime, browser, load profiler, shell syntax suite, or Discord test was run for this audit. Findings below are distinguished from behaviors that need runtime reproduction.

The active panel path is React/Vite + Express/TypeScript with JSON files under `/opt/snck-panel/.data`. The repository also contains a Laravel migration foundation; it is not the active store. Existing audit documents remain historical evidence, not proof that the current revision was retested.

## Confirmed design/feature gaps

| Area | Finding | Evidence / impact | Status |
|---|---|---|---|
| Admin navigation | `src/pages/AdminDashboard.tsx` renders Overview, Users, Fleet, Security, and Settings as local state tabs in one route. | Does not satisfy the requested independent page/route per admin section; refresh/deep-link cannot select a tab. | Open |
| Admin sidebar | `src/components/Sidebar.tsx` maps Admin to `/admin`, and Fleet to `/admin/servers`; there is no dedicated admin-area shell/sidebar in `src/App.tsx`. | Admin navigation is mixed with the user panel shell. | Open |
| Admin section coverage | Requested Settings, Users, Servers, Nodes, Free Service, Discord Bot, Themes, Logs, and Security pages are not all separate admin routes. | Existing API keys and theme screens exist elsewhere; they are not a complete routed admin information architecture. | Open |
| Free service | `src/server/routes/allocations.ts` gates free creation on `settings.freeService?.enabled` and exposes available allocations. | The inspected code does not demonstrate full plan CRUD (RAM/CPU/disk/per-user caps), claim history, or resource grants. | Open |
| Discord rewards bot | No validated implementation was established by the inspected routes and manifests. | Token validation, invite cache, member-stay tracking, tier awards, slash commands, token encryption lifecycle, and status monitoring remain unverified/open. Do not treat a settings form alone as a bot. | Open |
| Account uniqueness | The inspected auth router exposes setup/register/login but does not apply rate-limit middleware at those route declarations. | The existing security helper is process-local and had unbounded bucket growth before this branch. One-account-per-person enforcement (hashed IP/device identity, trusted-proxy handling, exceptions) was not established. | Partially addressed: route rate limits added on branch; identity policy remains open |
| CSRF/session model | `src/server/middleware/auth.ts` uses a Bearer JWT authorization header. | Cookie/session CSRF requirements are not directly met by this auth model; do not add cookie assumptions without auditing frontend token storage and origin checks. | Open for dedicated review |
| Socket authorization | `server.ts` authenticates Socket.IO handshake JWTs and checks server ownership/sub-user access in `joinServer` in the current source excerpt. | Historical `AUDIT_FINDINGS_2026-09.md` flags this area; retest current code and confirm room/event emissions remain authorized. | Runtime regression test required |
| Performance | App uses lazy-loaded routes and manual Vite chunks; server console polls remote logs every 3 seconds per subscribed remote server. | No profiler trace or before/after measurements are available. This polling may be intentional; actual scroll lag cause remains unproven. | Open |
| JSON persistence | `src/server/services/db.ts` serializes writes per file within one process. | This is not a cross-process/distributed transaction system; JSON store remains active despite SQL schema/migration assets. | Open / architectural constraint |
| Installer | README documents the existing GitHub raw one-command installer and installer menu. | No fresh Ubuntu/Debian installation was run for this branch. Keep it unchanged until tested from a fresh VPS. | Runtime verification required |
| Quality checks | Existing audit documents record earlier successful checks. | They do not prove this branch passes. No tests were run in this environment. | Pending CI and local runtime |

## Changes made on this branch

1. `src/server/services/security.ts`
   - Added periodic expiry sweeping for process-local rate-limit buckets.
   - Added a hard cap so requests from many unique IP/path pairs cannot grow the in-memory bucket map indefinitely.
   - Added `Retry-After` on rejected requests.
   - Added validation for malformed encrypted-secret payload shape before attempting decryption.
2. `src/server/routes/auth.ts`
   - Applied conservative per-IP/path limits to login, Google login, password/username changes, registration, and initial owner setup.
   - The implementation is process-local. Multi-instance deployments need a shared rate-limit store, and real-world shared IPs may need an explicit exception/override mechanism.

## Required follow-up before declaring production-ready

- Run `npm ci`, `npm run lint`, `npm run build`, `npm test`, `npm --prefix node-daemon ci`, `npm --prefix node-daemon run build`, and `bash -n install.sh scripts/snck-menu.sh scripts/install-panel.sh node-daemon/install.sh`.
- Start the built panel and exercise all registered frontend routes as owner, admin, and ordinary user; assert unauthorized API requests fail.
- Add automated tests for login/register rate limiting, trusted proxy configuration, identity-hash lifecycle, account exception/whitelist behavior, and concurrency.
- Implement and integration-test separate admin routes and a dedicated admin layout.
- Implement Free Service plan CRUD and claims with server-side resource enforcement.
- Implement Discord bot lifecycle and invite reward tracking only with a test guild, test users, and test bot token; test reconnects, rate limits, leavers, bots, self-invites, alt signals, tier idempotency, and encrypted-at-rest secret handling.
- Record real browser performance profiles before/after changes (long tasks, render duration, memory, and polling/network volume). Do not claim performance improvement without comparable captures.
- Validate the one-command installer on fresh Ubuntu and Debian VPSs and confirm backups/rollback before rollout.
