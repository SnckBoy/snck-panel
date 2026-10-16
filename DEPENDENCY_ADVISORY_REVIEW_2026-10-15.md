# Snck dependency advisory review — 2026-10-15

## Sources

- [OSV: @grpc/grpc-js advisory](https://api.osv.dev/v1/vulns/GHSA-m9gg-hp2v-232j)
- [GitHub: @grpc/grpc-js advisory](https://github.com/grpc/grpc-node/security/advisories/GHSA-m9gg-hp2v-232j)
- [GitHub: braces upstream issue](https://github.com/micromatch/braces/issues/84)
- [OSV: braces advisory](https://osv.dev/vulnerability/GHSA-vfj7-8cjw-p6xm)

## Findings

The original dependency tree contained two affected `@grpc/grpc-js` versions: Dockerode used `1.14.4`, and Firebase Firestore pulled `1.9.16`. The advisory fixes are `1.13.6` and `1.14.5`; the repository now overrides all copies to `1.14.6`.

The original tree also contained the high-severity `braces` advisory through PM2's proxy and file-watching dependencies. Upstream currently reports no patched `braces` release. Rather than applying npm's breaking PM2 downgrade, the lockfile now uses current compatible releases for PM2's transitive chain: `chokidar@5.0.0`, `js-yaml@5.4.3`, `proxy-agent@8.0.2`, `pac-proxy-agent@9.1.0`, `get-uri@8.0.1`, and `basic-ftp@6.2.3`. The unused direct `http-proxy-middleware` dependency was removed after confirming that Snck source code does not import it.

Firebase remains on the current compatible major version because it is used by the browser-side Google authentication components. No forced major downgrade was applied.

## Remediation and verification

`package.json` contains targeted npm overrides for the patched grpc and PM2 transitive versions. `npm ci`, the full `npm audit`, the production-only audit, the panel lint/build, the node-daemon build, the first-run Owner authentication regression, and all installer shell syntax checks passed.

The final audit result is zero informational, low, moderate, high, and critical vulnerabilities for the resolved dependency tree. Re-run `npm audit` after future dependency updates, and revisit the braces advisory if upstream publishes a new release or changes its advisory status.
