export const NAVIGATION_GROUPS = [
  { id: "workspace", title: "Workspace", items: [
    { id: "overview", label: "Overview", adminOnly: false },
    { id: "servers", label: "Servers", adminOnly: false },
    { id: "nodes", label: "Nodes", adminOnly: false },
  ] },
  { id: "admin", title: "Administration", items: [
    { id: "deploy", label: "Create server", adminOnly: true },
    { id: "admin-control", label: "Admin control", adminOnly: true },
    { id: "fleet", label: "Server fleet", adminOnly: true },
    { id: "api-keys", label: "API keys", adminOnly: true },
    { id: "cloudflare", label: "Cloudflare", adminOnly: true },
    { id: "snck-theme", label: "Snck Theme", adminOnly: true },
  ] },
  { id: "config", title: "Configuration", items: [
    { id: "settings", label: "Settings", adminOnly: false },
  ] },
] as const;

export const DASHBOARD_SECTIONS = [
  { id: "welcome", label: "Welcome / fleet hero", description: "Panel greeting, fleet summary, deploy and refresh actions." },
  { id: "quick-actions", label: "Quick actions widget", description: "Shortcuts to deploy a server, inspect servers, and manage nodes." },
  { id: "metrics", label: "Metric cards", description: "Panel CPU, host memory, servers online, and active containers." },
  { id: "system-health", label: "System health widget", description: "Live host resource summary and fleet status." },
  { id: "server-inventory", label: "Server inventory", description: "Search, filter, and manage deployed server cards." },
] as const;

export const DASHBOARD_METRICS = [
  { id: "cpu", label: "Panel CPU" },
  { id: "ram", label: "Host memory" },
  { id: "net", label: "Servers online" },
  { id: "nodes", label: "Active containers" },
] as const;

/** Only routes and components that currently exist in Snck Panel are theme-configurable. */
export const SERVER_NAVIGATION = [
  { id: "overview", label: "Overview" },
  { id: "console", label: "Console" },
  { id: "properties", label: "Properties" },
  { id: "players", label: "Players" },
  { id: "files", label: "File manager" },
  { id: "sftp", label: "SFTP details" },
  { id: "subusers", label: "Sub-users" },
  { id: "plugins", label: "Plugins" },
  { id: "mods", label: "Mods" },
  { id: "modpacks", label: "Modpacks" },
  { id: "settings", label: "Settings" },
  { id: "backups", label: "Backups" },
  { id: "playit", label: "Playit tunnel" },
] as const;

export type PanelStructure = {
  shell: "snck" | "pterodactyl";
  navigationMode: "sidebar" | "topbar";
  sidebarMode: "expanded" | "compact";
  contentWidth: "standard" | "wide" | "full";
  dashboardMode: "overview" | "server-first";
  serverNavigationMode: "sidebar" | "tabs";
  serverNavigationOrder: string[];
  hiddenServerNavigation: string[];
};

export type ThemeLayout = {
  navigationOrder: string[];
  hiddenNavigation: string[];
  dashboardOrder: string[];
  hiddenDashboard: string[];
  metricsOrder: string[];
  hiddenMetrics: string[];
  structure: PanelStructure;
};

const navIds = NAVIGATION_GROUPS.flatMap((group) => group.items.map((item) => item.id));
const dashboardIds = DASHBOARD_SECTIONS.map((section) => section.id);
const metricIds = DASHBOARD_METRICS.map((metric) => metric.id);
const serverNavIds = SERVER_NAVIGATION.map((item) => item.id);

export const DEFAULT_THEME_LAYOUT: ThemeLayout = {
  navigationOrder: navIds,
  hiddenNavigation: [],
  dashboardOrder: ["welcome", "quick-actions", "metrics", "system-health", "server-inventory"],
  hiddenDashboard: ["quick-actions", "system-health"],
  metricsOrder: metricIds,
  hiddenMetrics: [],
  structure: {
    shell: "snck",
    navigationMode: "sidebar",
    sidebarMode: "expanded",
    contentWidth: "standard",
    dashboardMode: "overview",
    serverNavigationMode: "sidebar",
    serverNavigationOrder: ["overview", "console", "players", "files", "properties", "sftp", "subusers", "plugins", "mods", "modpacks", "settings", "backups", "playit"],
    hiddenServerNavigation: [],
  },
};

export const PTERODACTYL_LAYOUT: ThemeLayout = {
  ...DEFAULT_THEME_LAYOUT,
  navigationOrder: ["overview", "servers", "nodes", "deploy", "fleet", "admin-control", "api-keys", "cloudflare", "snck-theme", "settings"],
  hiddenNavigation: [],
  dashboardOrder: ["server-inventory", "metrics", "welcome", "quick-actions", "system-health"],
  hiddenDashboard: ["welcome", "quick-actions", "system-health"],
  metricsOrder: ["cpu", "ram", "net", "nodes"],
  hiddenMetrics: [],
  structure: {
    shell: "pterodactyl",
    navigationMode: "sidebar",
    sidebarMode: "expanded",
    contentWidth: "wide",
    dashboardMode: "server-first",
    serverNavigationMode: "sidebar",
    serverNavigationOrder: ["console", "files", "players", "properties", "plugins", "mods", "modpacks", "backups", "sftp", "subusers", "settings", "overview", "playit"],
    hiddenServerNavigation: [],
  },
};

const catalog = {
  navigationOrder: new Set(navIds),
  dashboardOrder: new Set(dashboardIds),
  metricsOrder: new Set(metricIds),
  serverNavigationOrder: new Set(serverNavIds),
};
const publicNavigation = new Set(NAVIGATION_GROUPS.flatMap((group) => group.items.filter((item) => !item.adminOnly).map((item) => item.id)));

const isPermutation = (value: unknown, expected: Set<string>) =>
  Array.isArray(value) && value.length === expected.size &&
  value.every((entry) => typeof entry === "string" && expected.has(entry)) &&
  new Set(value).size === expected.size;
const isSubset = (value: unknown, expected: Set<string>) =>
  Array.isArray(value) && value.every((entry) => typeof entry === "string" && expected.has(entry)) &&
  new Set(value).size === value.length;

const isStructure = (value: unknown): value is PanelStructure => {
  if (!value || typeof value !== "object" || Array.isArray(value)) return false;
  const structure = value as Record<string, unknown>;
  const keys = ["shell", "navigationMode", "sidebarMode", "contentWidth", "dashboardMode", "serverNavigationMode", "serverNavigationOrder", "hiddenServerNavigation"];
  if (Object.keys(structure).length !== keys.length || Object.keys(structure).some((key) => !keys.includes(key))) return false;
  return ["snck", "pterodactyl"].includes(String(structure.shell)) &&
    ["sidebar", "topbar"].includes(String(structure.navigationMode)) &&
    ["expanded", "compact"].includes(String(structure.sidebarMode)) &&
    ["standard", "wide", "full"].includes(String(structure.contentWidth)) &&
    ["overview", "server-first"].includes(String(structure.dashboardMode)) &&
    ["sidebar", "tabs"].includes(String(structure.serverNavigationMode)) &&
    isPermutation(structure.serverNavigationOrder, catalog.serverNavigationOrder) &&
    isSubset(structure.hiddenServerNavigation, catalog.serverNavigationOrder) &&
    (structure.serverNavigationOrder as string[]).some((id) => !(structure.hiddenServerNavigation as string[]).includes(id));
};

export function isValidThemeLayout(value: unknown): value is ThemeLayout {
  if (!value || typeof value !== "object" || Array.isArray(value)) return false;
  const layout = value as Record<string, unknown>;
  const legacyKeys = ["navigationOrder", "hiddenNavigation", "dashboardOrder", "hiddenDashboard", "metricsOrder", "hiddenMetrics"];
  const keys = [...legacyKeys, "structure"];
  // Backward compatibility: old packages remain valid and are upgraded by normalizeThemeLayout.
  if (Object.keys(layout).length !== legacyKeys.length && (Object.keys(layout).length !== keys.length || Object.keys(layout).some((key) => !keys.includes(key)))) return false;
  if (Object.keys(layout).some((key) => !keys.includes(key))) return false;
  if (!isPermutation(layout.navigationOrder, catalog.navigationOrder) || !isSubset(layout.hiddenNavigation, catalog.navigationOrder) ||
      !isPermutation(layout.dashboardOrder, catalog.dashboardOrder) || !isSubset(layout.hiddenDashboard, catalog.dashboardOrder) ||
      !isPermutation(layout.metricsOrder, catalog.metricsOrder) || !isSubset(layout.hiddenMetrics, catalog.metricsOrder)) return false;
  if (Object.keys(layout).length === keys.length && !isStructure(layout.structure)) return false;
  return (layout.navigationOrder as string[]).some((id) => publicNavigation.has(id) && !(layout.hiddenNavigation as string[]).includes(id)) &&
    (layout.dashboardOrder as string[]).some((id) => !(layout.hiddenDashboard as string[]).includes(id));
}

export function normalizeThemeLayout(value: unknown): ThemeLayout {
  if (!isValidThemeLayout(value)) return structuredClone(DEFAULT_THEME_LAYOUT);
  const legacy = value as Omit<ThemeLayout, "structure"> & { structure?: PanelStructure };
  return {
    navigationOrder: [...legacy.navigationOrder],
    hiddenNavigation: [...legacy.hiddenNavigation],
    dashboardOrder: [...legacy.dashboardOrder],
    hiddenDashboard: [...legacy.hiddenDashboard],
    metricsOrder: [...legacy.metricsOrder],
    hiddenMetrics: [...legacy.hiddenMetrics],
    structure: legacy.structure ? {
      ...legacy.structure,
      serverNavigationOrder: [...legacy.structure.serverNavigationOrder],
      hiddenServerNavigation: [...legacy.structure.hiddenServerNavigation],
    } : structuredClone(DEFAULT_THEME_LAYOUT.structure),
  };
}
