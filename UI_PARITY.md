# Laravel UI parity checklist

Updated 2026-11-01 on `php-migration-final`. Each item below maps an original React route/page or user-visible feature to a Laravel Blade/Livewire surface. API-only implementations are not counted as complete. `PARTIAL` is used only for the explicitly deferred live Discord rewards work.

| Original page or feature | Original route/source | Laravel UI surface | Status |
|---|---|---|---|
| First-run setup | `/setup`, `src/pages/Setup.tsx` | `AuthController` setup Blade view plus `/install` alias | DONE |
| Login | `/login`, `Login.tsx` | `AuthController` login Blade view | DONE |
| Registration | `/register`, `Register.tsx` | `AuthController` register Blade view | DONE |
| Core checkpoint | `/core-checkpoint`, `CoreCheckpoint.tsx` | Laravel authenticated dashboard/setup flow | DONE |
| Dashboard | `/`, `Dashboard.tsx` | `Livewire\Dashboard`, dashboard Blade | DONE |
| Server list | `/servers`, `ServerList.tsx` | `Livewire\ServerIndex`, server index Blade | DONE |
| Create server | `/servers/create`, `CreateServer.tsx` | `Livewire\ServerCreate`, create Blade | DONE |
| Server overview/lifecycle | `/servers/:id`, `ServerView.tsx` | `Livewire\ServerDetail`, overview and lifecycle controls | DONE |
| Console and command execution | server `console` tab | ServerDetail console tab with polling and command form | DONE |
| Logs | server logs view | ServerDetail logs tab with polling/filter | DONE |
| Online players | server players view | ServerDetail players tab with polling/list command | DONE |
| server.properties | server properties view | ServerDetail properties editor | DONE |
| File manager | server files view | ServerDetail file browser/editor/upload/archive controls | DONE |
| Backups | server backups view | ServerDetail backup create/download/delete UI | DONE |
| Sub-users | server subusers view | ServerDetail delegated-access UI | DONE |
| SFTP | server SFTP view | ServerDetail SFTP credential and transport details UI | DONE |
| Plugins | server plugins view | ServerDetail Plugins tab with Modrinth install/list/remove | DONE |
| Mods | server mods view | ServerDetail Mods tab with Modrinth install/list | DONE |
| Modpacks | server modpacks view | ServerDetail Modpacks tab linked to validated file/import workflow | DONE |
| Playit tunnel | `PlayitTunnel.tsx`, server Playit tab | ServerDetail Playit tab with start/stop/reset/status/claim link | DONE |
| Nodes | `/nodes`, `Nodes.tsx` | `Livewire\InfrastructureManager`, nodes tab | DONE |
| Allocations | `/allocations`, `Allocations.tsx` | InfrastructureManager allocations tab | DONE |
| Cloudflare | `/cloudflare`, `Cloudflare.tsx` | `Livewire\AdminWorkspace` Cloudflare tab: connect and zone listing | DONE |
| API keys | `/api-keys`, `ApiKeysPage.tsx` | AdminWorkspace API keys tab: create/revoke/scope display | DONE |
| Admin dashboard | `/admin`, `AdminDashboard.tsx` | Admin users and AdminWorkspace pages | DONE |
| Admin servers | `/admin/servers`, `AdminServers.tsx` | Server list/create/detail staff controls | DONE |
| Admin user configuration | system admin users | `Livewire\AdminUsers` page | DONE |
| Global settings | `/settings`, `SettingsPage.tsx` | AdminWorkspace Settings tab with persisted settings | DONE |
| Snck Theme | `/snck-theme`, `SnckTheme.tsx` | `Livewire\ThemeManager` plus export route | DONE |
| Discord rewards user page | `/free-service/discord`, `DiscordRewards.tsx` | AdminWorkspace Discord configuration; live claim page still requires the deferred rewards batch | PARTIAL |
| Discord OAuth/account linking | Discord page actions | Encrypted configuration and strict validation exist; live OAuth callback/linking is deferred | PARTIAL |
| Discord invite attribution and gateway | Discord worker/page actions | No completed Laravel gateway worker yet; deferred for real Discord API implementation | PARTIAL |
| Discord reward claiming/provisioning | Discord page actions | Plan CRUD exists; live provisioning and reconciliation remain deferred | PARTIAL |
| Authenticated navigation/layout | React `Layout` | Laravel app layout and route navigation | DONE |
| Google authentication | AuthExperience/Login actions | AuthController Google redirect/callback | DONE |
| Settings/theme persistence | React SettingsProvider | Laravel settings table and ThemeManager | DONE |
| Tutorial/accessibility controls | React tutorial/reduced motion settings | persisted Laravel settings controls in AdminWorkspace | DONE |

## Verification

The UI routes compile with `php artisan view:cache` and `php artisan route:list`. Server feature coverage is exercised by the existing feature suite; the new admin workspace and SFTP transport are covered by focused tests in this batch. The list intentionally distinguishes UI surfaces from daemon operations: a UI action is complete only when it is wired to the Laravel service or frozen node-daemon contract.
