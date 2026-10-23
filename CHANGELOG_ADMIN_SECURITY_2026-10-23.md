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
- Added source-level audit findings, confidence limits, and explicit remaining work.
- Documented that previous audit documents are historical and are not evidence that this branch was tested.
- Recorded required verification commands and end-to-end tests.

## Verification

Not run in this environment. GitHub could not be cloned into the execution environment because network/DNS access failed. No claim is made that typecheck, lint, build, shell syntax, route click-through, profiler, duplicate-account, or Discord tests passed. Run the repository CI and the commands listed in the audit report before deploying.
