export const NAVIGATION_GROUPS = [
  { id: "workspace", title: "Your server", items: [
    { id: "overview", label: "Overview", adminOnly: false },
    { id: "nodes", label: "Nodes", adminOnly: false },
    { id: "servers", label: "Servers", adminOnly: false },
  ] },
  { id: "admin", title: "Admin", items: [
    { id: "deploy", label: "Deploy", adminOnly: true },
    { id: "admin-control", label: "Admin control", adminOnly: true },
    { id: "fleet", label: "Fleet", adminOnly: true },
    { id: "api-keys", label: "API Keys", adminOnly: true },
    { id: "cloudflare", label: "Cloudflare", adminOnly: true },
    { id: "snck-theme", label: "Snck Theme", adminOnly: true },
  ] },
  { id: "config", title: "Config", items: [
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
  { id: "ram", label: "Host Memory" },
  { id: "net", label: "Servers Online" },
  { id: "nodes", label: "Active Containers" },
] as const;

export type ThemeLayout = {
  navigationOrder: string[];
  hiddenNavigation: string[];
  dashboardOrder: string[];
  hiddenDashboard: string[];
  metricsOrder: string[];
  hiddenMetrics: string[];
};

export const DEFAULT_THEME_LAYOUT: ThemeLayout = {
  navigationOrder: NAVIGATION_GROUPS.flatMap((group) => group.items.map((item) => item.id)),
  hiddenNavigation: [],
  dashboardOrder: ["welcome", "quick-actions", "metrics", "system-health", "server-inventory"],
  hiddenDashboard: ["quick-actions", "system-health"],
  metricsOrder: DASHBOARD_METRICS.map((metric) => metric.id),
  hiddenMetrics: [],
};

const catalog = {
  navigationOrder: new Set(NAVIGATION_GROUPS.flatMap((group) => group.items.map((item) => item.id))),
  dashboardOrder: new Set(DASHBOARD_SECTIONS.map((section) => section.id)),
  metricsOrder: new Set(DASHBOARD_METRICS.map((metric) => metric.id)),
};
const publicNavigation: Set<string> = new Set(NAVIGATION_GROUPS.flatMap((group) => group.items.filter((item) => !item.adminOnly).map((item) => item.id)));

const isPermutation = (value: unknown, expected: Set<string>) =>
  Array.isArray(value) && value.length === expected.size &&
  value.every((entry) => typeof entry === "string" && expected.has(entry)) &&
  new Set(value).size === expected.size;
const isSubset = (value: unknown, expected: Set<string>) =>
  Array.isArray(value) && value.every((entry) => typeof entry === "string" && expected.has(entry)) &&
  new Set(value).size === value.length;

export function isValidThemeLayout(value: unknown): value is ThemeLayout {
  if (!value || typeof value !== "object" || Array.isArray(value)) return false;
  const layout = value as Record<string, unknown>;
  const required = Object.keys(DEFAULT_THEME_LAYOUT);
  if (Object.keys(layout).length !== required.length || Object.keys(layout).some((key) => !required.includes(key))) return false;
  const navigationOrder = layout.navigationOrder;
  const hiddenNavigation = layout.hiddenNavigation;
  const dashboardOrder = layout.dashboardOrder;
  const hiddenDashboard = layout.hiddenDashboard;
  const metricsOrder = layout.metricsOrder;
  const hiddenMetrics = layout.hiddenMetrics;
  if (!isPermutation(navigationOrder, catalog.navigationOrder) || !isSubset(hiddenNavigation, catalog.navigationOrder) ||
      !isPermutation(dashboardOrder, catalog.dashboardOrder) || !isSubset(hiddenDashboard, catalog.dashboardOrder) ||
      !isPermutation(metricsOrder, catalog.metricsOrder) || !isSubset(hiddenMetrics, catalog.metricsOrder)) return false;
  return (navigationOrder as string[]).some((id) => publicNavigation.has(id) && !(hiddenNavigation as string[]).includes(id)) &&
    (dashboardOrder as string[]).some((id) => !(hiddenDashboard as string[]).includes(id));
}

export function normalizeThemeLayout(value: unknown): ThemeLayout {
  if (!isValidThemeLayout(value)) return {
    ...DEFAULT_THEME_LAYOUT,
    navigationOrder: [...DEFAULT_THEME_LAYOUT.navigationOrder],
    hiddenNavigation: [...DEFAULT_THEME_LAYOUT.hiddenNavigation],
    dashboardOrder: [...DEFAULT_THEME_LAYOUT.dashboardOrder],
    hiddenDashboard: [...DEFAULT_THEME_LAYOUT.hiddenDashboard],
    metricsOrder: [...DEFAULT_THEME_LAYOUT.metricsOrder],
    hiddenMetrics: [...DEFAULT_THEME_LAYOUT.hiddenMetrics],
  };
  return {
    navigationOrder: [...value.navigationOrder], hiddenNavigation: [...value.hiddenNavigation],
    dashboardOrder: [...value.dashboardOrder], hiddenDashboard: [...value.hiddenDashboard],
    metricsOrder: [...value.metricsOrder], hiddenMetrics: [...value.hiddenMetrics],
  };
}
