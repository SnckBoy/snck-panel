import { ArrowDown, ArrowUp, Eye, EyeOff, LayoutDashboard, Navigation, PanelsTopLeft, Server } from "lucide-react";
import { DASHBOARD_METRICS, DASHBOARD_SECTIONS, NAVIGATION_GROUPS, SERVER_NAVIGATION, type ThemeLayout } from "../shared/themeLayout";

type Item = { id: string; label: string; description?: string };

function reorderSubset(order: string[], itemIds: string[], from: number, to: number) {
  const subset = order.filter((id) => itemIds.includes(id));
  if (to < 0 || to >= subset.length) return order;
  [subset[from], subset[to]] = [subset[to], subset[from]];
  let index = 0;
  const set = new Set(itemIds);
  return order.map((id) => set.has(id) ? subset[index++] : id);
}

function SortableVisibilityList({ title, description, items, order, hidden, onOrder, onHidden }: {
  title: string; description: string; items: Item[]; order: string[]; hidden: string[];
  onOrder: (next: string[]) => void; onHidden: (next: string[]) => void;
}) {
  const ids = items.map((item) => item.id);
  const sorted = order.filter((id) => ids.includes(id)).map((id) => items.find((item) => item.id === id)!).filter(Boolean);
  const toggle = (id: string) => onHidden(hidden.includes(id) ? hidden.filter((item) => item !== id) : [...hidden, id]);
  return <section className="snx-layout-editor-list">
    <div className="snx-layout-editor-list-title"><div><strong>{title}</strong><p>{description}</p></div><span>{items.length} options</span></div>
    <ol>{sorted.map((item, index) => {
      const isHidden = hidden.includes(item.id);
      return <li key={item.id} className={isHidden ? "is-hidden" : ""}>
        <label><input type="checkbox" checked={!isHidden} onChange={() => toggle(item.id)} /><span className="snx-layout-item-copy"><strong>{item.label}</strong>{item.description && <small>{item.description}</small>}</span></label>
        <div className="snx-layout-item-actions"><button type="button" title={isHidden ? `Add ${item.label}` : `Remove ${item.label}`} aria-label={isHidden ? `Show ${item.label}` : `Hide ${item.label}`} onClick={() => toggle(item.id)}>{isHidden ? <EyeOff size={15} /> : <Eye size={15} />}</button><button type="button" title={`Move ${item.label} up`} aria-label={`Move ${item.label} up`} disabled={index === 0} onClick={() => onOrder(reorderSubset(order, ids, index, index - 1))}><ArrowUp size={15} /></button><button type="button" title={`Move ${item.label} down`} aria-label={`Move ${item.label} down`} disabled={index === sorted.length - 1} onClick={() => onOrder(reorderSubset(order, ids, index, index + 1))}><ArrowDown size={15} /></button></div>
      </li>;
    })}</ol>
  </section>;
}

export default function ThemeLayoutEditor({ value, onChange }: { value: ThemeLayout; onChange: (layout: ThemeLayout) => void }) {
  const set = <K extends keyof ThemeLayout>(key: K, next: ThemeLayout[K]) => onChange({ ...value, [key]: next });
  const setStructure = <K extends keyof ThemeLayout["structure"]>(key: K, next: ThemeLayout["structure"][K]) =>
    onChange({ ...value, structure: { ...value.structure, [key]: next } });
  const structure = value.structure;
  return <div className="snx-layout-editor">
    <div className="snx-layout-editor-intro"><LayoutDashboard size={18} /><div><strong>Full panel structure</strong><p>Choose a panel shell, navigation layout, content width, dashboard arrangement, and server-management navigation. Imported themes can change these supported structures—not just colors. Access permissions and server actions are never changed by a theme.</p></div></div>
    <section className="snx-layout-editor-list">
      <div className="snx-layout-editor-list-title"><div><strong>Panel structure preset</strong><p>These settings change the actual application layout.</p></div><PanelsTopLeft size={17}/></div>
      <div className="snx-theme-form-grid">
        <label>Shell preset<select value={structure.shell} onChange={(e) => setStructure("shell", e.target.value as typeof structure.shell)}><option value="snck">Snck modern</option><option value="pterodactyl">Pterodactyl-inspired hosting</option></select></label>
        <label>Primary navigation<select value={structure.navigationMode} onChange={(e) => setStructure("navigationMode", e.target.value as typeof structure.navigationMode)}><option value="sidebar">Left sidebar</option><option value="topbar">Top navigation bar</option></select></label>
        <label>Sidebar width<select value={structure.sidebarMode} onChange={(e) => setStructure("sidebarMode", e.target.value as typeof structure.sidebarMode)}><option value="expanded">Expanded</option><option value="compact">Compact icon rail</option></select></label>
        <label>Content width<select value={structure.contentWidth} onChange={(e) => setStructure("contentWidth", e.target.value as typeof structure.contentWidth)}><option value="standard">Standard</option><option value="wide">Wide</option><option value="full">Full width</option></select></label>
        <label>Dashboard layout<select value={structure.dashboardMode} onChange={(e) => setStructure("dashboardMode", e.target.value as typeof structure.dashboardMode)}><option value="overview">Operations overview</option><option value="server-first">Server-first hosting dashboard</option></select></label>
        <label>Server navigation<select value={structure.serverNavigationMode} onChange={(e) => setStructure("serverNavigationMode", e.target.value as typeof structure.serverNavigationMode)}><option value="sidebar">Server sidebar</option><option value="tabs">Horizontal tabs</option></select></label>
      </div>
    </section>
    {NAVIGATION_GROUPS.map((group) => <SortableVisibilityList key={group.id} title={`${group.title} navigation`} description="Reorder or show/hide these built-in links." items={group.items.map((item) => ({ id: item.id, label: item.label }))} order={value.navigationOrder} hidden={value.hiddenNavigation} onOrder={(next) => set("navigationOrder", next)} onHidden={(next) => set("hiddenNavigation", next)} />)}
    <SortableVisibilityList title="Dashboard sections and widgets" description="Reorder or hide the actual built-in dashboard sections." items={DASHBOARD_SECTIONS.map((item) => ({ id: item.id, label: item.label, description: item.description }))} order={value.dashboardOrder} hidden={value.hiddenDashboard} onOrder={(next) => set("dashboardOrder", next)} onHidden={(next) => set("hiddenDashboard", next)} />
    <SortableVisibilityList title="Live metric cards" description="Choose which real metrics appear and in what order." items={DASHBOARD_METRICS.map((item) => ({ id: item.id, label: item.label }))} order={value.metricsOrder} hidden={value.hiddenMetrics} onOrder={(next) => set("metricsOrder", next)} onHidden={(next) => set("hiddenMetrics", next)} />
    <SortableVisibilityList title="Server management navigation" description="Reorder server tabs such as Console, Files, Players, Plugins, Settings, and Backups. A tab only appears when the corresponding feature is available for that server." items={SERVER_NAVIGATION.map((item) => ({ id: item.id, label: item.label }))} order={structure.serverNavigationOrder} hidden={structure.hiddenServerNavigation} onOrder={(next) => setStructure("serverNavigationOrder", next)} onHidden={(next) => setStructure("hiddenServerNavigation", next)} />
    <div className="snx-layout-safe-note"><Navigation size={15} /><span>The theme changes presentation and navigation only. Hidden links do not grant or revoke access; backend authorization and route permissions remain enforced.</span><Server size={15} /></div>
  </div>;
}
