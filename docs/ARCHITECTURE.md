# SNCK PANEL Architecture

## Control-plane model

SNCK PANEL is a control plane. Browser clients communicate with the panel API and Socket.IO layer. The panel communicates with trusted node daemons. Minecraft workloads run on nodes, normally inside Docker containers.

```text
Browser
  |
  +-- HTTPS / REST
  +-- WSS / Socket.IO
  |
SNCK Panel
  |
  +-- Auth / Authorization
  +-- Server API
  +-- Node API
  +-- Backup / File / Minecraft metadata services
  +-- Relational persistence
  |
Node Daemon
  |
  +-- Docker runtime
  +-- Filesystem sandbox
  +-- Logs / console
  +-- Resource telemetry
  |
Minecraft containers
```

## Boundaries

### Client

The browser is untrusted. It may request operations, but it never determines ownership, roles, permissions, node identity, resource limits or successful operation state.

### Panel API

The panel is authoritative for authentication, authorization, metadata, allocations and orchestration. Sensitive operations are validated before reaching a node.

### Node daemon

The daemon is a privileged infrastructure component. It authenticates panel requests, validates payloads, confines filesystem operations to server roots, and performs Docker operations locally.

### Minecraft runtime

Minecraft workloads are isolated from the panel. Downloaded plugins/mods execute only inside the intended Minecraft workload and never in the panel process.

## Persistence migration

The current application has a JSON repository layer. The production target is a relational repository layer with migrations. The migration must:

- preserve legacy data;
- create a backup before mutation;
- validate every record;
- preserve identifiers where safe;
- detect duplicates;
- be idempotent;
- produce a report;
- leave source JSON untouched until an operator explicitly retires it.

The application should use repository/service interfaces so persistence implementation can change without duplicating business logic.

## Authorization model

Every server operation follows:

1. authenticate the caller;
2. load the target server;
3. determine ownership/sub-user/admin access;
4. check the operation-specific permission;
5. perform the operation;
6. record the result;
7. emit a state event only after the operation is accepted/succeeds as appropriate.

Socket.IO follows the same model before joining server rooms.

## Runtime model

Docker is the primary runtime. Local execution is an explicit compatibility mode and must not become an accidental fallback that bypasses node isolation or resource enforcement.

## Network model

Nodes should be reachable through a private network, VPN, Tailscale or another authenticated transport when public IPv4 is unavailable. Docker's Unix socket must remain local to the daemon and must never be published as a public service.

## Security model

Centralized services should provide:

- safe filesystem path resolution;
- validated command arguments;
- external-download allowlists/host validation;
- bounded uploads and archives;
- rate limits;
- structured errors;
- request IDs;
- audit records;
- secret redaction.

## Observability

Panel and node operations should produce structured logs with request/operation IDs. Audit logs capture privileged actions without passwords, API secrets, tokens or private keys.
