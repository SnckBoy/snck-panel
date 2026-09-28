# SNCK PANEL Production Roadmap

This document records the production completion plan after the SNCK/JTG audit.

## Architecture rules

- SNCK's existing node daemon and Socket.IO authorization remain authoritative where they are stronger than the reference implementation.
- JTG functionality is ported by behavior, not by copying historical patch scripts or branding.
- Production persistence must move from JSON files to a relational database through an idempotent migration.
- Node agents must be authenticated and must never expose Docker directly to the public Internet.
- File operations must resolve paths inside the server root before every filesystem operation.
- Downloaded archives and artifacts must be validated before extraction or installation.
- UI state is never authoritative for permissions or operation success.

## Completion gates

### Foundation
- [x] Repository and reference-source audit
- [x] JTG feature parity document
- [x] SNCK architecture document
- [x] Central safe-path service
- [x] SNCK package metadata
- [ ] Relational persistence adapter
- [ ] Idempotent legacy JSON migration
- [ ] Central authorization policy service
- [ ] Central structured API error contract

### Node control plane
- [ ] Node registration lifecycle
- [ ] Signed/authenticated daemon requests
- [ ] Heartbeat persistence
- [ ] Capacity accounting
- [ ] Reconnect/backoff
- [ ] Remote lifecycle operations
- [ ] Remote file operations
- [ ] Migration protocol

### Minecraft operations
- [ ] Real server creation transaction
- [ ] Docker resource enforcement
- [ ] Console stream
- [ ] Live statistics
- [ ] File manager
- [ ] Chunked uploads
- [ ] Backups
- [ ] Properties/settings
- [ ] Version metadata service
- [ ] Plugin manager
- [ ] Mod manager
- [ ] Modpack manager
- [ ] World manager
- [ ] Player manager
- [ ] SFTP
- [ ] Sub-users
- [ ] API keys
- [ ] Playit integration

### Platform
- [ ] Admin dashboard
- [ ] Audit log
- [ ] Notifications
- [ ] Global search
- [ ] Health/readiness endpoints
- [ ] Update/rollback workflow
- [ ] Panel installer
- [ ] Node installer

### Verification
- [ ] TypeScript clean
- [ ] Frontend production build
- [ ] Unit tests
- [ ] integration tests
- [ ] security regression tests
- [ ] fresh Ubuntu installation test
- [ ] real Docker-node lifecycle test
- [ ] real Minecraft lifecycle test
- [ ] migration rehearsal

A feature is only considered complete after implementation and validation. UI presence alone is not completion.
