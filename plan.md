# VPS control panel implementation plan

## Approach
Extend the existing authenticated React/Express hosting panel with a dedicated VPS dashboard and admin resource-policy page. Reuse the existing authentication, navigation, routing, icon library, and dark visual language; use translucent glass surfaces, responsive layout, and accessible controls. VPS actions will provide truthful UI state and clear feedback without claiming a host operation succeeded. The repository has Docker services but no LXD/SSHX backend integration. Do not execute privileged `lxc` commands or expose arbitrary host execution. Production container provisioning, persistent state, and SSHX session creation remain contingent on a separately secured LXD host adapter and server-side authorization.

## Project structure
- `src/pages/VpsPanel.tsx`: VPS overview, resource configuration, OS choice, create/install/lifecycle/reinstall actions, SSHX availability state, and admin policies.
- `src/App.tsx`: authenticated `/vps` route and admin-only `/admin/vps` route.
- `src/components/Sidebar.tsx`: navigation entry points.
- `src/components/Layout.tsx`: VPS breadcrumbs.
- `src/index.css`: responsive glass dashboard styling.

## Design
Three rare style directions considered: **Frosted observability console** (0.06), **warm editorial server room** (0.04), **monochrome command shell** (0.08). Choose Frosted observability console: precise infra dashboard with layered translucent panels, cool cyan accent, deep ink backdrop, sparse ambient highlights, and restrained motion. Principles: legibility before decoration; distinct hierarchy for user actions vs. host status; glass is subtle and opaque enough for contrast; responsive cards with compact technical metadata. Layout uses an asymmetric header/metrics band followed by a fleet list and a narrow provisioning rail. Signature elements: cyan runtime-health beacon and monospaced resource readouts. Interaction: controls are stateful and report the demo/integration boundary; destructive reinstall has confirmation. Animation: short fades and gentle hover lift, respecting reduced motion. Typography: reuse Inter plus JetBrains Mono. Brand essence: clear infrastructure control for VPS users; calm, transparent, capable. Voice: concise and operational (e.g. “Your container controls, in one place.” “Host connection required to provision.”). Wordmark concept: SNCK mark beside a compact VPS indicator. Signature color: cool cyan.

## Material constraints
Existing login/register routes already exist. The user's archive is a Discord bot that invokes `lxc` subprocesses and applies privileged/nesting/AppArmor/device/capability overrides. Those settings are not reused. A host adapter is required before actual VPS creation, start/stop/restart/reinstall, resource enforcement, or SSHX URL issuance can be represented as live operations. The UI must not fabricate a working SSHX URL or claim a container has been provisioned.
