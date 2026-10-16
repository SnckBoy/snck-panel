import { ArrowDown, ArrowUp, Eye, EyeOff, LayoutDashboard, Navigation, PanelsTopLeft } from "lucide-react";
import { DASHBOARD_METRICS, DASHBOARD_SECTIONS, NAVIGATION_GROUPS, type ThemeLayout } from "../shared/themeLayout";

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
  return <div className="snx-layout-editor">
    <div className="snx-layout-editor-intro"><LayoutDashboard size={18} /><div><strong>Panel layout</strong><p>Reorder or hide supported elements. Changes apply to everyone using this theme; protected pages and permissions remain unchanged.</p></div></div>
    {NAVIGATION_GROUPS.map((group) => <SortableVisibilityList key={group.id} title={`${group.title} navigation`} description="Reorder or show/hide these built-in links." items={group.items.map((item) => ({ id: item.id, label: item.label }))} order={value.navigationOrder} hidden={value.hiddenNavigation} onOrder={(next) => set("navigationOrder", next)} onHidden={(next) => set("hiddenNavigation", next)} />)}
    <SortableVisibilityList title="Dashboard sections and widgets" description="Enable or remove built-in sections/widgets and change their position." items={DASHBOARD_SECTIONS.map((item) => ({ id: item.id, label: item.label, description: item.description }))} order={value.dashboardOrder} hidden={value.hiddenDashboard} onOrder={(next) => set("dashboardOrder", next)} onHidden={(next) => set("hiddenDashboard", next)} />
    <SortableVisibilityList title="Metric cards" description="Choose which live metric cards appear and in what order." items={DASHBOARD_METRICS.map((item) => ({ id: item.id, label: item.label }))} order={value.metricsOrder} hidden={value.hiddenMetrics} onOrder={(next) => set("metricsOrder", next)} onHidden={(next) => set("hiddenMetrics", next)} />
    <div className="snx-layout-safe-note"><Navigation size={15} /><span>Navigation changes only affect visible shortcuts. Account roles, protected routes, server actions, and permissions are not changed.</span><PanelsTopLeft size={15} /></div>
  </div>;
}
