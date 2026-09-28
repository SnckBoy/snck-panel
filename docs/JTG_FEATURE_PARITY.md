# SNCK PANEL — JTG Feature Parity

This document tracks the functional capabilities identified in the supplied JTG reference archives and their destination in SNCK PANEL. JTG is a functional reference only; SNCK remains the authoritative architecture and branding.

## Core application

| Feature | SNCK destination | Status | Notes |
|---|---|---|---|
| Login / registration | `src/server/routes/auth.ts`, frontend auth | Preserved | Existing SNCK auth remains authoritative. |
| Dashboard | frontend dashboard | Preserved/merge | Keep SNCK shell; absorb useful JTG resource cards. |
| Server list | `src/server/routes/servers.ts` | Preserved | Extend rather than duplicate. |
| Server creation | server controller/runtime services | Merge | JTG version/runtime selection is a reference. |
| Server view | frontend server control center | Merge | Consolidate duplicate panels into one SNCK implementation. |
| Account management | auth/account routes | Merge | Harden validation and session handling. |
| Admin controls | admin routes/pages | Merge | Backend role checks are authoritative. |
| API keys | `src/server/routes/api-keys.ts` | Preserved/extend | Add scopes, expiration and secure secret handling. |
| Notifications | frontend + notification persistence | Merge | Deduplicate repetitive events. |
| Global search | frontend search service | Merge | Target servers, users, nodes and supported metadata. |

## Infrastructure

| Feature | SNCK destination | Status | Notes |
|---|---|---|---|
| Multi-node management | `src/server/routes/nodes.ts` | Preserved | SNCK node architecture is preferred over JTG replacement. |
| Node daemon | `node-daemon/` | Preserved | Existing daemon/installer are the baseline. |
| Node heartbeat | node-agent API | Preserved | Expand telemetry and health states. |
| Docker runtime | `src/server/services/docker.ts`, daemon runtime | Preserved | Docker remains the primary isolation layer. |
| Local runtime | runtime abstraction | Merge | Retain only where operationally justified. |
| Node installer | `node-daemon/install.sh` | Preserved/extend | Fresh Ubuntu validation required. |
| Playit | server/node integration | Merge | Must use controlled process execution. |
| Allocations | `src/server/routes/allocations.ts` | Preserved/extend | Enforce uniqueness transactionally after DB migration. |
| Cloudflare compatibility | proxy/origin configuration | Preserved/extend | WebSocket and forwarded headers require deployment tests. |

## Server operations

| Feature | Destination | Status |
|---|---|---|
| Start / stop / restart | server lifecycle controllers | Preserved |
| Kill | server lifecycle controller | Merge |
| Suspend / resume | server lifecycle controller | Preserved/extend |
| Real console | Socket.IO + node daemon | Preserved/extend |
| Live logs | Socket.IO + runtime | Preserved/extend |
| Live statistics | node telemetry | Preserved/extend |
| Command execution | node daemon | Preserved/harden |
| Server migration | transfer service | Planned implementation |
| Resource limits | runtime/node services | Extend |

## Files and storage

| Feature | Destination | Status |
|---|---|---|
| File browser | server file service/frontend | Preserved/extend |
| Upload | multer/chunk service | Preserved/extend |
| Chunked upload | upload controller | Preserved/extend |
| Download | file controller | Preserved/extend |
| Create/rename/delete | file controller | Preserved |
| Zip/unzip | archive service | Preserved/harden |
| File editor | frontend + bounded API | Extend |
| Path traversal protection | centralized safe-path service | Required |
| SFTP | `src/server/services/sftp.ts` | Preserved/extend |

## Minecraft ecosystem

| Feature | Destination | Status |
|---|---|---|
| Vanilla | version/runtime service | Extend |
| Paper | version/runtime service | Extend |
| Fabric | version/runtime service | Extend |
| Forge | version/runtime service | Extend |
| NeoForge | version/runtime service | Extend |
| Version discovery | metadata service | Extend |
| Plugin search/install | plugin service | Merge |
| Plugin update/remove | plugin service | Merge |
| Mod search/install | mod service | Merge |
| Mod update/remove | mod service | Merge |
| Modrinth | external metadata client | Merge |
| Modpacks | modpack service | Merge |
| World manager | world service | Merge |
| Player manager | Minecraft command/query service | Merge |
| Server properties | properties service | Merge |

## Operations

| Feature | Destination | Status |
|---|---|---|
| Backups | backup service | Preserved/extend |
| Backup restore | backup service | Extend |
| Retention | backup scheduler/service | Extend |
| Sub-users | server permissions service | Preserved/extend |
| Granular permissions | authorization service | Required |
| Audit logs | audit service | Required |
| Health / readiness | HTTP health service | Extend |
| Updates | update service | Extend |

## Merge policy

1. Keep an existing SNCK implementation when it is functionally stronger or safer.
2. Port useful JTG behavior into the SNCK architecture instead of copying project structure.
3. Replace JTG mock providers with real node/runtime operations.
4. Do not import historical `fix_*` scripts into production runtime.
5. Migrate JSON persistence to relational storage through an idempotent migration layer.
6. All privileged operations must be authorized on the server.

## Validation status

This matrix is the implementation baseline. A feature is not considered complete merely because a route or UI exists; completion requires an actual integration test against a working node/runtime where applicable.
