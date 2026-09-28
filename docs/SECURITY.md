# SNCK PANEL Security Model

## Trust boundaries

SNCK has four trust zones: browser client, panel API, node daemon, and Minecraft containers. The browser is untrusted. Frontend role checks are presentation-only; the API is authoritative.

## Authorization

Every server-scoped operation must resolve the authenticated principal and verify ownership or an explicit sub-user permission before touching the server. IDs supplied by the client are never treated as proof of access.

Socket.IO rooms follow the same rule. A socket must authenticate before joining a server room, and the server must verify access to that server before emitting logs, statistics, console events, or power events.

## Filesystem isolation

All server-relative paths must pass the centralized path policy. Absolute paths, traversal, Windows drive paths, null bytes, and symlink escapes are rejected. Archive extraction must validate every archive member before writing it.

## Command execution

User input must never be interpolated into `sh -c`/`bash -c` command strings. Prefer direct process spawning with validated argument arrays and explicit allowlists.

## Docker

The host Docker socket must never be exposed to Minecraft containers. Containers should receive only their own server storage and explicitly required resources. Resource limits must match values shown in the panel.

## External downloads

Plugin, mod, version, and modpack downloads must use controlled HTTPS sources where practical, enforce timeouts and size limits, and validate archives before extraction. Arbitrary URL fetches must not become an SSRF primitive.

## Secrets

Passwords are hashed. API/node secrets are not logged. Newly generated secrets should be displayed only when required and stored using a representation that limits exposure.

## Errors

Production responses must not expose stack traces, database credentials, internal filesystem paths, node credentials, or session tokens.

## Operational requirements

Run the panel behind HTTPS or a trusted private network, keep Docker and node-agent ports private, configure an explicit production origin allowlist, rotate node credentials when compromise is suspected, and maintain backups before upgrades or migrations.
