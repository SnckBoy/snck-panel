# UI parity checklist

Updated on `discord-final`.

| Feature | Laravel UI | Status |
|---|---|---|
| Login, setup, registration | AuthController Blade pages | DONE |
| Dashboard and server list/create | Livewire Dashboard, ServerIndex, ServerCreate | DONE |
| Server overview/lifecycle | ServerDetail | DONE |
| Console and logs | ServerDetail tabs | DONE |
| Players and properties | ServerDetail tabs | DONE |
| Files and backups | ServerDetail tabs | DONE |
| Sub-users | ServerDetail tab | DONE |
| SFTP credentials | ServerDetail SFTP tab | DONE |
| SFTP SSH transport | Node SSH2 SFTP listener with per-server jail and Laravel node-auth callback; protocol integration test added, combined test run pending | IMPLEMENTED — VERIFY |
| Plugins, mods, modpacks | ServerDetail manager tabs | DONE |
| Playit | ServerDetail Playit tab | DONE |
| Nodes and allocations | InfrastructureManager | DONE |
| Themes | ThemeManager | DONE |
| API keys and administrator settings | Existing admin APIs/pages and AdminWorkspace settings/API-key tabs | DONE |
| Cloudflare DNS | CloudflareManager: zone selection, list/create/edit/delete for A, AAAA, CNAME, TXT, MX, SRV, Minecraft SRV helper | DONE |
| Discord account linking | DiscordRewardsPage OAuth link/unlink | DONE |
| Discord reward progress/claims | DiscordRewardsPage with duplicate-safe claims | DONE |
| Discord claim administration | DiscordClaimsAdmin approve/reject/reconcile | DONE |
| Discord invite attribution Gateway | No persistent WebSocket Gateway worker yet | PARTIAL |
| Discord automatic provisioning | Claims are recorded and reviewable; automatic ServerProvisioner execution remains | PARTIAL |
| Discord retention/member reconciliation | Artisan maintenance command and installer timer | DONE |

`PARTIAL` is intentionally limited to the two live Discord worker/provisioning capabilities that were not fabricated. The remaining work is recorded in `PROGRESS.md`.
