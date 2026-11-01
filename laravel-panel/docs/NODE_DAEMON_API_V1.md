# Snck Node Daemon HTTP Contract — v1 Frozen

**Contract ID:** `snck-node-daemon-v1`  
**Source snapshot:** `SnckBoy/snck-panel` main, commit `580aae915d0c9e5d57d735ad2aa56a382587f577`  
**Status:** frozen during Phase 2; no daemon route or root Express route was changed in this phase.

Phase 3 must implement this contract in the PHP NodeClient or negotiate a separately versioned contract. Do not rename or change a v1 method/path while a deployed daemon depends on it. Keep the current Node/Express panel routes available until the daemon update is deployed and verified. A breaking daemon change requires a new versioned route (for example `/v2`), not a silent reinterpretation of v1.

## Authentication and transport

Every daemon route in the table is protected by `Authorization: Bearer <per-node credential>`. The daemon compares the configured token in constant time. A panel user's cookie or role is not a node credential. The daemon makes an outbound callback to `POST /api/node-agent/heartbeat` with JSON `{ "nodeId": "...", "stats": { ... } }` and the node bearer token.

For the migrated system, panel-to-daemon connections must use HTTPS with normal hostname and certificate-chain verification enabled. Verification cannot be disabled. An operator-provided PEM CA may extend the trust bundle. Redirects must not forward bearer credentials. This transport policy does not change the route method or path list below.

## Complete daemon route list

| # | Method | Path | Request / response |
|---:|---|---|---|
| 1 | GET | `/v1/health` | No body. JSON health payload includes `ok`, `daemonVersion`, `cpuUsage`, `memory`, `disk`, `network`, `docker`, `uptime`, `hostname`, `servers`, `timestamp`. `docker` is an explicit boolean. |
| 2 | GET | `/v1/stats` | No body. JSON system stats payload (same stats fields, without the health wrapper). |
| 3 | POST | `/v1/daemon/restart` | No body. `202` JSON `{success:true,restarting:true,message}`; daemon exits after accepting the request. |
| 4 | POST | `/v1/servers` | JSON create payload: `id`, `type`, `version`, `javaVersion`, optional `image`, `port`, `ram` (GB), `cpu`, optional `rconPassword`. `201` JSON `{containerId}`. |
| 5 | POST | `/v1/servers/:id/start` | No body. JSON `{success:true}` on success. |
| 6 | POST | `/v1/servers/:id/stop` | No body. JSON `{success:true}` on success. |
| 7 | POST | `/v1/servers/:id/restart` | No body. JSON `{success:true}` on success. |
| 8 | POST | `/v1/servers/:id/kill` | No body. JSON `{success:true}` on success. |
| 9 | DELETE | `/v1/servers/:id` | No body. Stops/removes the Docker container; JSON `{success:true}` on success. |
| 10 | GET | `/v1/servers/:id/status` | No body. Docker container inspect JSON. |
| 11 | GET | `/v1/servers/:id/stats` | No body. JSON includes `available`, `timestamp`, CPU/RAM, disk and network values; unavailable responses return `available:false`, nullable stats and an explicit error. |
| 12 | GET | `/v1/servers/:id/logs` | No body. `text/plain` container logs (tail 200). |
| 13 | POST | `/v1/servers/:id/command` | JSON `{command}`. JSON `{success:true,output}`; command limit 1–4096 characters. |
| 14 | POST | `/v1/servers/:id/files/list` | JSON `{path}`. JSON array `{name,isDirectory,size}`. |
| 15 | POST | `/v1/servers/:id/files/read` | JSON `{path}`. JSON `{isFile:true,content}`. |
| 16 | POST | `/v1/servers/:id/files/write` | JSON `{path,content}` (UTF-8). JSON `{success:true}`. |
| 17 | POST | `/v1/servers/:id/files/write-base64` | JSON `{path,content}` with base64 bytes. JSON `{success:true,bytes}`. |
| 18 | POST | `/v1/servers/:id/files/write-chunk` | JSON `{uploadId,path,offset,totalSize,content}`. JSON `{success,offset,bytes,complete}`; decoded chunk max 8 MiB. |
| 19 | POST | `/v1/servers/:id/files/replace-batch` | JSON `{files:[{path,content}],confirmReplace?}`. May return `409` with `requiresConfirmation`; success JSON includes file count and optional backup path. |
| 20 | POST | `/v1/servers/:id/files/mkdir` | JSON `{path}`. JSON `{success:true}`. |
| 21 | POST | `/v1/servers/:id/files/rename` | JSON `{oldPath,newPath}`. JSON `{success:true}`. |
| 22 | POST | `/v1/servers/:id/files/delete` | JSON `{paths:[...]}`. JSON `{success:true}`. |
| 23 | POST | `/v1/servers/:id/files/zip` | JSON `{dirPath?,fileNames:[...],outputName}`. JSON `{success:true,filename}`. |
| 24 | POST | `/v1/servers/:id/files/unzip` | JSON `{path}`. Safely extracts ZIP to its containing directory; JSON `{success:true}`. |
| 25 | GET | `/v1/servers/:id/files/download` | Query `path` or repeated `paths`. Returns file bytes or `application/zip` stream. |
| 26 | GET | `/v1/servers/:id/backups` | No body. JSON array of `{filename,size,createdAt}`. |
| 27 | GET | `/v1/servers/:id/backups/:filename` | No body. Streams the named `.tar.gz` backup. |
| 28 | DELETE | `/v1/servers/:id/backups/:filename` | No body. JSON `{success:true}`. |
| 29 | POST | `/v1/servers/:id/backups` | No body. JSON `{success:true,filename,sizeBytes,checksum}` after creating a tar.gz backup. |

`:id` is restricted by the daemon to `[A-Za-z0-9_-]+`. File paths are resolved inside that server's storage root and symlink escapes are rejected. Backup filenames must match `backup-[A-Za-z0-9_.-]+.tar.gz`.

## Compatibility findings to resolve in Phase 3

- The prototype PHP client previously called `POST /v1/health`; v1 is `GET /v1/health`. Phase 2 corrects health to GET and refuses success without an explicit `ok:true` and boolean `docker` value.
- File list/read are daemon `POST` routes, not `GET`; downloads are a streamed `GET`, not JSON. The Phase 3 client must preserve binary bodies and the exact daemon method/path.
- Daemon JSON parser is currently capped at 20 MiB, while some individual transfer handlers claim 80 MiB or 512 MiB request limits. Do not rely on those larger declared values until parser/route limits are reconciled in a separately reviewed daemon change.
- The daemon's own process uses HTTP only if deployment exposes it that way; the migrated panel must not downgrade to HTTP. Deploy the daemon behind verified HTTPS before switching the PHP panel to it.

## Contract verification

From the repository root, run `cd laravel-panel && php artisan test --filter=NodeDaemonContractTest`. The test compares the frozen manifest to the checked-out daemon source and fails if a route is missing, added, or changed without an explicit contract update.
