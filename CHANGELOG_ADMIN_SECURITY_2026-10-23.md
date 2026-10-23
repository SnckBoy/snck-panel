# Changelog — admin audit and security slice

Date: 2026-10-23
Branch: `feat/admin-audit-free-service-security`

## Changed files

### `src/server/services/security.ts`
- Bound the in-memory rate-limit bucket map to 50,000 entries to prevent unbounded growth from unique IP/path traffic.
- Periodically remove expired buckets.
- Return a `Retry-After` header with 429 responses.
- Reject malformed encrypted-secret envelopes before attempting AES-GCM decryption.

### `src/server/routes/auth.ts`
- Added per-IP/path limits to login and Google login endpoints.
- Added stricter limits to registration and first-owner setup.
- Added limits to authenticated password and username change endpoints.
- Note: limits are process-local. Multi-instance deployment needs a shared store; shared-IP exception/override policy remains unimplemented.

### `AUDIT_ADMIN_SECURITY_2026-10-23.md`
- Added source-level admin/security/feature gap analysis and follow-up requirements.
- Updated with observed GitHub Actions results and separate Cloudflare preview failure.

## Verification observed on 2026-10-23

GitHub Actions workflow `SNCK PANEL CI`, run 412, completed successfully for the PR merge commit `8eebba9eb441283c9efe4d98867824967ced812e`:
- `npm ci`: passed
- `npm test`: passed (theme validation/layout tests)
- `npm run lint` (TypeScript `tsc --noEmit`): passed
- `npm run build`: passed
- Node daemon `npm ci`: passed
- Node daemon `npm run build`: passed
- Shell syntax checks and non-systemd PM2 fallback test: passed
- GitHub combined status check: Vercel success

Separate Cloudflare preview deployment for commit `aaf01ae` reported **Build: Failed**. Its detailed build log is behind the authenticated Cloudflare dashboard and was not accessible through the connected tools, so the root cause is not established and no fix is claimed for that deployment failure. This is not a Git merge conflict: GitHub reports the PR as mergeable.

No local build was run in this environment. No browser route click-through, profiler, duplicate-account, fresh-VPS installer, or Discord integration test is claimed. The PR remains a draft and should not be merged/deployed until the Cloudflare build failure is diagnosed and the remaining feature work is implemented and tested.
