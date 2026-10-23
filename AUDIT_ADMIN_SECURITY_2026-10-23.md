# Admin, security, and operations audit — 2026-10-23

## Scope and confidence

This combines a source-level repository review with the GitHub Actions results available for PR #30. The working copy could not be cloned into the interactive execution environment because network/DNS access failed, so the CI runner's results are the build/test evidence. No local browser session, runtime deployment, performance profiler, fresh-VPS install, or Discord test was run.

The active panel path is React/Vite + Express/TypeScript with JSON files under `/opt/snck-panel/.data`. The repository also contains a Laravel migration foundation; it is not the active store.

## Verification observed

GitHub Actions workflow `SNCK PANEL CI`, run 412, completed successfully for PR merge commit `8eebba9eb441283c9efe4d98867824967ced812e`:
- `npm ci`: passed
- `npm test`: passed (theme validation/layout tests)
- `npm run lint` (`tsc --noEmit`): passed
- `npm run build`: passed
- Node daemon `npm ci`: passed
- Node daemon `npm run build`: passed
- Shell syntax checks and non-systemd PM2 fallback test: passed
- GitHub combined status check: Vercel success

Separate Cloudflare preview deployment for commit `aaf01ae` reported **Build: Failed**. The detailed log is behind the authenticated Cloudflare dashboard and was not accessible through the connected tools. Root cause is unknown; no Cloudflare fix is claimed. GitHub reports PR #30 as `mergeable: true`, so this is not a Git merge conflict. The PR is still a draft.

## Confirmed design/feature gaps

| Area | Finding | Status |
|---|---|---|
| Admin navigation | `src/pages/AdminDashboard.tsx` renders Overview, Users, Fleet, Security, and Settings as local state tabs rather than independent routes. | Open |
| Admin sidebar | `src/components/Sidebar.tsx` maps Admin to `/admin`; there is no separate admin shell/sidebar in `src/App.tsx`. | Open |
| Admin section coverage | Settings, Users, Servers, Nodes, Free Service, Discord Bot, Themes, Logs, and Security are not all separate admin routes. | Open |
| Free service | Existing allocation route gates creation using `settings.freeService?.enabled`, but full plan CRUD, per-user caps, claims, and resource grants are not established. | Open |
| Discord rewards bot | Token validation, invite/member tracking, tiers, slash commands, encrypted token lifecycle and status monitoring are not established as a tested feature. | Open |
| Account uniqueness | Route-level rate limits were added, but one-account-per-IP/device enforcement, hashed identity lifecycle, trusted-proxy policy, and admin exceptions are not implemented. | Partially addressed |
| CSRF/session model | Auth middleware uses Bearer JWTs; cookie/session CSRF requirements need a dedicated review of token storage and origin protections. | Open |
| Socket authorization | Historical audit flags Socket.IO room authorization. Current code has ownership checks in `joinServer`, but runtime regression tests remain required. | Runtime test required |
| Performance | Routes already use lazy loading and Vite chunks; no before/after profiler captures exist. Root cause of scroll lag remains unproven. | Open |
| JSON persistence | JSON writes are serialized within one process but are not a distributed transaction system. SQL migration foundation is not the active store. | Architectural follow-up |
| Installer | No fresh Ubuntu/Debian install was run. | Runtime verification required |

## Changes made on this branch

1. `src/server/services/security.ts`
   - Added periodic expiry sweeping for process-local rate-limit buckets.
   - Added a hard cap on bucket count.
   - Added `Retry-After` on 429 responses.
   - Added malformed encrypted-secret envelope checks.
2. `src/server/routes/auth.ts`
   - Applied per-IP/path limits to login, Google login, password/username changes, registration, and initial owner setup.
   - Limits are process-local; multi-instance deployments need a shared store. Shared-IP override/whitelist behavior is not implemented.

## Required follow-up

- Open the Cloudflare build log and copy the first actual error plus surrounding lines before changing deployment configuration. Dashboard link: https://dash.cloudflare.com/?to=/08e9ddba0af62d9b632023d1a816b3ec/workers/services/view/snck-panel/production/builds/7fbb6040-987f-40ad-932f-63a4b08ca2b2
- After fixing the Cloudflare-specific cause, rerun that deployment.
- Exercise all routes as owner, admin, and regular user; verify unauthorized API requests fail.
- Implement and test separate admin routes, Free Service plan CRUD/claims, and Discord bot lifecycle/rewards.
- Add tests for trusted proxy configuration, identity hashes, account exception/whitelist behavior, and concurrency.
- Capture comparable browser performance profiles before/after any performance fix.
- Validate the one-command installer on fresh Ubuntu and Debian VPSs and confirm backups/rollback before rollout.
