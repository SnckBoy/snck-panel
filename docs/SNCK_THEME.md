# Snck Theme

Snck Theme is the administrator-only theme manager for the React/Vite Snck Panel. Each theme stores safe visual tokens and a validated layout made from existing, supported panel elements. Theme packages are never treated as executable code or arbitrary stylesheets.

## Access and permissions

Sign in as an `admin` or `owner` and choose **Snck Theme** in the administrator navigation. The frontend route is admin-gated and every management endpoint independently validates the current authenticated administrator. Ordinary users cannot import or edit global themes.

## Theme editor

The editor has two tabs:

- **Appearance** edits the panel's color palette, typography (system, Inter, or monospace), text scale, content density, card elevation, and corner radius. Tokens are applied through existing CSS variables and a small allowlist of safe values.
- **Layout builder** changes the actual panel structure: Snck or Pterodactyl-inspired shell, sidebar or top navigation, expanded or compact navigation, standard/wide/full content width, overview or server-first dashboard, and server sidebar or horizontal server tabs. It also reorders or shows/hides supported panel navigation, dashboard sections, metric cards, and existing server-management links. Settings apply globally when the theme is active.

Navigation changes only affect shortcuts displayed in the sidebar. Hiding a link does not grant or revoke access: route guards, roles, permissions, and server-action authorization remain unchanged. The builder does not create arbitrary pages, upload new components, or run user-provided code; customization is limited to the supported catalog.

**Save theme** persists a draft without changing the active panel. **Save & apply** persists and activates it globally. **Apply** switches all panel pages to a theme. **Enable / Disable** controls library availability; disabling the active theme returns to Snck Default. **Export** downloads a ZIP. Protected built-in themes cannot be removed. **Restore default** restores the protected, always-available Snck Default appearance and layout.

Built-in themes currently include Snck Default, Midnight, Royal Purple, Nebula, Cyber Blue, Aurora, and Obsidian. Custom IDs are not limited to this list.

## Theme package format

ZIP uploads are limited to 2 MiB, 100 entries, and 32 KiB for the manifest. A package contains exactly one `theme.json` manifest (for example `my-theme/theme.json`). Other file contents are rejected; directory entries are ignored. The ZIP may be wrapped in a containing folder. Path traversal, absolute paths, drive paths, oversized entries, malformed manifests, duplicate IDs, and incompatible panel versions are rejected. The package is parsed without extracting its contents.

```json
{
  "id": "ember-night",
  "name": "Ember Night",
  "version": "1.0.0",
  "author": "Example Studio",
  "description": "Warm orange accents on a deep charcoal surface.",
  "supportedPanelVersion": ">=0.2.0",
  "config": {
    "primary": "#ff8747",
    "secondary": "#b8512d",
    "accent": "#ffc08a",
    "background": "#14100f",
    "surface": "#211a18",
    "sidebar": "#100d0c",
    "navbar": "#1b1513",
    "text": "#fff4ed",
    "muted": "#b9a69d",
    "border": "#46332c",
    "success": "#39d98a",
    "warning": "#f0b24a",
    "error": "#f26b78",
    "radius": 16,
    "fontFamily": "inter",
    "fontScale": 1,
    "density": "comfortable",
    "elevation": "soft",
    "surfaceOpacity": 76
  },
  "layout": {
    "navigationOrder": ["overview", "nodes", "servers", "deploy", "admin-control", "fleet", "api-keys", "cloudflare", "snck-theme", "settings"],
    "hiddenNavigation": [],
    "dashboardOrder": ["welcome", "quick-actions", "metrics", "system-health", "server-inventory"],
    "hiddenDashboard": ["quick-actions", "system-health"],
    "metricsOrder": ["cpu", "ram", "net", "nodes"],
    "hiddenMetrics": []
  }
}
```

All color values must be six-digit hex values. `radius` is 0–32, `fontFamily` is `system`, `inter`, or `mono`, `fontScale` is 0.85–1.25, `density` is `compact`, `comfortable`, or `spacious`, and `elevation` is `flat`, `soft`, or `glow`, and `surfaceOpacity` is an integer from 55–100. Lower values make supported panel/card surfaces more translucent and enable a restrained backdrop blur; 100 keeps surfaces opaque. Older themes without this field default to 100. Layout order arrays must be complete permutations of known IDs; hidden arrays may contain only known IDs, without duplicates. At least one non-admin navigation item and one dashboard section must remain visible. Omitting the newer typography tokens or layout is supported: older color-only theme packages are filled with safe defaults.

The navigation IDs are `overview`, `nodes`, `servers`, `deploy`, `admin-control`, `fleet`, `api-keys`, `cloudflare`, `snck-theme`, and `settings`. Dashboard sections are `welcome`, `quick-actions`, `metrics`, `system-health`, and `server-inventory`; metrics are `cpu`, `ram`, `net`, and `nodes`. Server navigation IDs are `overview`, `console`, `properties`, `players`, `files`, `sftp`, `subusers`, `plugins`, `mods`, `modpacks`, `settings`, `backups`, and `playit`. The `structure` object supports `shell`, `navigationMode`, `sidebarMode`, `contentWidth`, `dashboardMode`, `serverNavigationMode`, `serverNavigationOrder`, and `hiddenServerNavigation`. Old manifests without `structure` remain compatible and receive the safe Snck layout defaults.

CSS, JavaScript, PHP, shell scripts, images, custom component code, and remote imports are not supported. This deliberately avoids pretending arbitrary CSS or code can be made safe by filtering. ZIP files are never extracted or executed.

Pterodactyl Glass changes the layout of existing Snck Panel features; it does not create missing backend capabilities such as databases, schedules, network allocations, startup variables, or activity logs. Those features require separate product/API implementation before they can appear as functional pages.

Ready-to-import samples are included at [`examples/themes/ember-night.zip`](../examples/themes/ember-night.zip) and [`examples/themes/pterodactyl-glass.zip`](../examples/themes/pterodactyl-glass.zip). Their source manifests are in the corresponding theme folders. Pterodactyl Glass is an original Pterodactyl-inspired preset with translucent surfaces; it does not copy Pterodactyl assets or claim to add features that are not already implemented in Snck Panel.

## Persistence and updates

Installed theme metadata, style tokens, and layout are stored in `.data/themes.json`; the globally active theme ID is stored in `.data/settings.json`. Both are within the runtime data directory that the installer preserves when replacing application source during updates. Older saved themes are normalized with safe defaults. No database migration is needed: the active runtime uses JSON files, not the dormant PostgreSQL/Laravel foundation.

Theme configuration is loaded from the server-backed settings endpoint, not browser local storage. If active theme state is missing, disabled, or invalid, the service resolves to Snck Default. Removing or disabling the active custom theme also switches to default. The default itself cannot be disabled or removed.

## Troubleshooting

- If a ZIP is rejected, make sure it contains one `theme.json`, declares `supportedPanelVersion`, and contains no files other than that manifest and optional directory entries.
- If the panel does not refresh in already-open sessions, reload the page; settings are broadcast through the existing `settings_updated` event.
- To repair an invalid active theme, use **Restore default**. The backend also falls back to Snck Default when resolving an unavailable selection.
- Back up `.data/themes.json` and `.data/settings.json` with the normal panel data backup before making manual changes.

## Developer commands

From the repository root:

```bash
npm ci
npm test
npm run lint
npm run build
```

A local development instance can be started with `npm run dev`. For a fresh supported host, run `curl -fsSL https://raw.githubusercontent.com/SnckBoy/snck-panel/main/install.sh | sudo bash` and choose **Install SNCK PANEL**. For an existing host, run the same command and choose **Update SNCK PANEL** (or run `sudo bash /opt/snck-panel/install.sh update` when the local installer is present). Theme data persists in `.data/` across normal source updates.
