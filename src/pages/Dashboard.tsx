import React, { useState, useEffect, useMemo, useRef } from 'react';
import {
  Server, Cpu, HardDrive, Activity, Terminal, Play,
  Square, RotateCw, Search, LayoutGrid, List, Shield, Globe, Clock, Zap, AlertTriangle, RefreshCw, ArrowUpRight
} from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useDashboardData } from '../hooks/useDashboardData';
import { useSettings } from '../context/SettingsContext';
import PulseRing from '../components/PulseRing';
import InfrastructureCore from '../components/InfrastructureCore';

const SparklineChart = ({ data, color }: { data: number[]; color: string }) => {
  // Guard empty/single-point histories so SVG coordinates stay finite before telemetry arrives.
  const safeData = data.filter((value) => Number.isFinite(value));
  const samples = safeData.length === 0 ? [0, 0] : safeData.length === 1 ? [safeData[0], safeData[0]] : safeData;
  const max = Math.max(...samples);
  const min = Math.min(...samples);
  const range = max - min || 1;
  const points = samples.map((value, index) => {
    const x = (index / (samples.length - 1)) * 100;
    const y = 100 - (((value - min) / range) * 80 + 10);
    return `${x},${y}`;
  }).join(' ');
  return (
    <svg className="snx-metric-sparkline" preserveAspectRatio="none" viewBox="0 0 100 100" aria-hidden="true">
      <polygon points={`0,100 ${points} 100,100`} fill={color} fillOpacity="0.08" />
      <polyline points={points} fill="none" stroke={color} strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
};

const StatusPill = ({ status }: { status: string }) => {
  const config: Record<string, { text: string; dot: string; anim: string }> = {
    online: { text: 'Online', dot: 'snx-status-dot--online', anim: 'snx-status-pulse' },
    offline: { text: 'Offline', dot: 'snx-status-dot--offline', anim: '' },
    starting: { text: 'Starting', dot: 'snx-status-dot--starting', anim: 'snx-status-pulse' },
    stopping: { text: 'Stopping', dot: 'snx-status-dot--stopping', anim: 'snx-status-pulse' },
    restarting: { text: 'Restarting', dot: 'snx-status-dot--restarting', anim: 'snx-status-pulse' },
  };
  const current = config[status] || config.offline;

  return (
    <span className={`snx-status-pill snx-status-pill--${status}`}>
      <span className="snx-status-dot-wrap" aria-hidden="true">
        {current.anim && <span className={`snx-status-dot snx-status-dot--halo ${current.dot} ${current.anim}`} />}
        <span className={`snx-status-dot ${current.dot}`} />
      </span>
      {current.text}
    </span>
  );
};

export default function Dashboard() {
  const { panelName } = useSettings();
  const { stats, statsHistory, servers: realServers, refetch, state, lastUpdated } = useDashboardData();
  const [search, setSearch] = useState('');
  const [view, setView] = useState(() => {
    try { return localStorage.getItem("shironex-server-view") === "list" ? "list" : "grid"; } catch { return "grid"; }
  });
  const [statusFilter, setStatusFilter] = useState("all");
  const searchRef = useRef<HTMLInputElement>(null);
  useEffect(() => { try { localStorage.setItem("shironex-server-view", view); } catch {} }, [view]);
  useEffect(() => {
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "/" && !event.ctrlKey && !event.metaKey && !(event.target as HTMLElement).closest("input, textarea, select, [contenteditable]")) {
        event.preventDefault(); searchRef.current?.focus();
      }
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);
  const navigate = useNavigate();
  const [actionInProgress, setActionInProgress] = useState<Record<string, boolean>>({});
  const [actionNotice, setActionNotice] = useState<{ tone: "info" | "success" | "error"; text: string; dockerUnavailable?: boolean } | null>(null);

  const servers = useMemo(() => realServers.map((server) => ({
    id: server.id,
    name: server.name || "Unnamed server",
    type: (server.software || "Unknown") + (server.version ? ` ${server.version}` : ""),
    ip: (server as any).address || server.ipAlias || `${window.location.hostname}:${server.port || 25565}`,
    status: server.status === "running" ? "online" : server.status,
    // Inventory exposes configured limits, not live container utilization.
    cpu: Number(server.cpu) || 0,
    ram: Number((server as any).ram) || 0,
    node: (server as any).nodeName || "Unassigned node",
    suspended: server.suspended,
  })), [realServers]);
  const onlineCount = servers.filter(server => server.status === "online").length;

  const STATS = useMemo(() => {
    const defaultData = Array(20).fill(0);
    const activeContainers = stats?.activeContainers || 0;
    const totalContainers = stats?.totalContainers || 0;
    const cpuData = statsHistory?.length ? statsHistory.map((item: any) => item.cpuUsage || 0) : defaultData;
    const ramData = statsHistory?.length ? statsHistory.map((item: any) => item.ramUsage || 0) : defaultData;
    const containersData = statsHistory?.length ? statsHistory.map((item: any) => item.activeContainers || 0) : defaultData;
    while (cpuData.length < 2) cpuData.unshift(0);
    while (ramData.length < 2) ramData.unshift(0);
    while (containersData.length < 2) containersData.unshift(0);

    return [
      { id: 'cpu', label: 'Panel CPU', value: stats ? `${(stats.cpuUsage || 0).toFixed(1)}%` : "—", ringValue: stats?.cpuUsage || 0, data: cpuData, color: '#00F2FE', icon: Cpu, caption: 'panel host load' },
      { id: 'ram', label: 'Host Memory', value: stats ? `${(stats.ramUsage || 0).toFixed(1)}%` : "—", ringValue: stats?.ramUsage || 0, data: ramData, color: '#9B51E0', icon: HardDrive, caption: 'panel host usage' },
      { id: 'net', label: 'Servers Online', value: `${(Array.isArray(realServers) ? realServers : []).filter((server) => server.status === 'online').length} / ${(Array.isArray(realServers) ? realServers : []).length}`, ringValue: (Array.isArray(realServers) && realServers.length) ? (realServers.filter((server) => server.status === 'online').length / realServers.length) * 100 : 0, data: defaultData, color: '#00FF87', icon: Activity, caption: 'healthy instances' },
      { id: 'nodes', label: 'Active Containers', value: `${activeContainers} / ${totalContainers}`, ringValue: totalContainers ? (activeContainers / totalContainers) * 100 : 0, data: containersData, color: '#f6c453', icon: Zap, caption: 'running workloads' },
    ];
  }, [stats, statsHistory, realServers]);

  const handleAction = async (id: string, action: string) => {
    setActionInProgress((previous) => ({ ...previous, [id]: true }));
    const label = action === "start" ? "Start" : action === "stop" ? "Stop" : action === "restart" ? "Restart" : "Kill";
    setActionNotice({ tone: "info", text: `${label} requested. Waiting for the server state to update…` });
    try {
      await axios.post(`/api/servers/${id}/${action}`);
      void refetch();
      setActionNotice({ tone: "success", text: `${label} command accepted.` });
    } catch (error: any) {
      console.error('Action failed', error);
      const details = error.response?.data || {};
      setActionNotice({
        tone: "error",
        text: details.error || `${label} command failed.`,
        dockerUnavailable: details.dockerUnavailable === true,
      });
    } finally {
      setActionInProgress((previous) => ({ ...previous, [id]: false }));
    }
  };

  const filteredServers = useMemo(() => {
    const query = search.toLowerCase();
    return servers.filter((server) => (statusFilter === 'all' || (statusFilter === 'online' ? server.status === 'online' : server.status !== 'online')) && `${server.name} ${server.id} ${server.ip} ${server.node}`.toLowerCase().includes(query));
  }, [search, servers, statusFilter]);

  return (
    <div className="dashboard-shell snx-dashboard-page min-h-screen text-foreground font-sans selection:bg-cyan-400/20 overflow-x-hidden">
      <div className="dashboard-ambient snx-dashboard-ambient fixed inset-0 z-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div className="snx-ambient-orb snx-ambient-orb--purple" />
        <div className="snx-ambient-orb snx-ambient-orb--cyan" />
        <div className="snx-dashboard-grid" />
      </div>

      <div className="snx-dashboard-inner relative z-10 mx-auto">
        <header className="snx-dashboard-header">
          <div className="snx-page-heading">
            <div className="snx-brand-mark snx-brand-mark--dashboard"><Server className="h-5 w-5" /></div>
            <div className="min-w-0">
              <div className="snx-eyebrow"><span className="snx-live-dot" /> Control plane / overview</div>
              <h1 className="snx-page-title truncate">{panelName || 'Panel Control'}</h1>
              <p className="snx-page-subtitle">Your infrastructure, clearly in view. Monitor, manage, and deploy.</p>
            </div>
          </div>

          <div className="snx-dashboard-tools">
            <label className="snx-search-field">
              <Search className="h-4 w-4" aria-hidden="true" />
              <span className="sr-only">Search servers</span>
              <input ref={searchRef} aria-label="Search servers" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search servers..." />
              <kbd>/</kbd>
            </label>
            <div className="snx-view-toggle" aria-label="Server view">
              <button type="button" onClick={() => setView('grid')} className={view === 'grid' ? 'is-active' : ''} aria-label="Grid view" aria-pressed={view === "grid"}><LayoutGrid className="h-4 w-4" /></button>
              <button type="button" onClick={() => setView('list')} className={view === 'list' ? 'is-active' : ''} aria-label="List view" aria-pressed={view === "list"}><List className="h-4 w-4" /></button>
            </div>
          </div>
        </header>

        {actionNotice && <div role="status" aria-live="polite" className={`mb-5 rounded-2xl border px-4 py-3 text-sm ${actionNotice.tone === "error" ? "border-rose-400/25 bg-rose-400/10 text-rose-100" : actionNotice.tone === "success" ? "border-emerald-400/25 bg-emerald-400/10 text-emerald-100" : "border-cyan-400/25 bg-cyan-400/10 text-cyan-100"}`}>
          {actionNotice.dockerUnavailable ? (
            <div className="flex items-start gap-3">
              <AlertTriangle className="mt-0.5 h-5 w-5 shrink-0 text-amber-300" aria-hidden="true" />
              <div className="space-y-1">
                <p className="font-semibold text-amber-200">Docker runtime unavailable</p>
                <p>{actionNotice.text}</p>
                <p className="text-amber-100/75">Install and start Docker on the affected node, then retry the server action.</p>
              </div>
            </div>
          ) : actionNotice.text}
        </div>}

        {(state === "error" || state === "partial") && <div role="alert" className="snx-data-notice"><AlertTriangle size={17} /><span>{state === "error" ? "Unable to refresh your workspace." : "Some telemetry is unavailable."} Last received data is retained.</span><button onClick={() => void refetch()}>Retry</button></div>}
        <section className="snx-fleet-hero" aria-label="Fleet overview">
          <div className="snx-fleet-intro">
            <span className="snx-eyebrow">YOUR OPERATIONS WORKSPACE</span>
            <h2>Built to keep<br /><span>you in control.</span></h2>
            <p>{state === "loading" ? "Connecting to your infrastructure…" : `${onlineCount} of ${servers.length} instances online. Your console, files, and resources are one click away.`}</p>
            <div className="flex flex-wrap gap-3"><button className="snx-primary-button" onClick={() => navigate('/servers/create')}>Deploy server <ArrowUpRight size={16} /></button><button className="snx-secondary-button" onClick={() => void refetch()}><RefreshCw size={15} /> Refresh fleet</button></div>
            <span className="snx-fleet-updated">{lastUpdated ? `Inventory received ${lastUpdated.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}` : "Waiting for first inventory response"}</span>
          </div>
          <InfrastructureCore servers={servers.map(server => ({ id: server.id, name: server.name, status: server.status }))} size="compact" label="Fleet topology" />
        </section>

        <section className="snx-metric-grid" aria-label="Cluster metrics">
          {STATS.map((stat, index) => {
            const Icon = stat.icon;
            return (
              <motion.article
                key={stat.id}
                initial={{ opacity: 0, y: 18 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: index * 0.07, duration: 0.45 }}
                className={`dashboard-stat-card qx-stat-card snx-metric-card snx-metric-card--${stat.id}`}
              >
                <div className="snx-card-sheen" aria-hidden="true" />
                <div className="snx-metric-card-header">
                  <div>
                    <p className="snx-card-label">{stat.label}</p>
                    <p className="snx-metric-value">{stat.value}</p>
                    <p className="snx-card-caption">{stat.caption}</p>
                  </div>
                  <div className="snx-metric-card-tools"><PulseRing value={stat.ringValue} size={50} label={stat.label} /><div className="snx-metric-icon" style={{ color: stat.color, ['--snx-icon-color' as string]: stat.color }}><Icon className="h-4 w-4" /></div></div>
                </div>
                <div className="snx-metric-chart"><SparklineChart data={stat.data} color={stat.color} /></div>
              </motion.article>
            );
          })}
        </section>

        <section className="snx-server-section">
          <div className="snx-section-heading">
            <div>
              <div className="snx-eyebrow">Runtime inventory</div>
              <h2 className="snx-section-title"><Server className="h-5 w-5" /> Deployed instances <span>{filteredServers.length}</span></h2>
            </div>
            <div className="snx-filter-tabs" aria-label="Filter server status">{[['all', 'All instances'], ['online', 'Online'], ['attention', 'Not online']].map(([key, label]) => <button key={key} aria-pressed={statusFilter === key} onClick={() => setStatusFilter(key)}>{label}</button>)}</div>
          </div>

          <motion.div layout className={view === 'grid' ? 'snx-server-grid' : 'snx-server-list'}>
            <AnimatePresence mode="popLayout">
              {filteredServers.map((server) => (
                <ServerCard
                  key={server.id}
                  server={server}
                  view={view}
                  isBusy={server.suspended || actionInProgress[server.id] || ['starting', 'stopping', 'restarting'].includes(server.status)}
                  onAction={(action: string) => handleAction(server.id, action)}
                  onOpenTerminal={() => navigate(`/servers/${server.id}`)}
                />
              ))}
            </AnimatePresence>
            {filteredServers.length === 0 && (
              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="snx-empty-state">
                <Search className="h-7 w-7" />
                <strong>{state === "loading" ? "Loading your instances…" : state === "error" && !lastUpdated ? "Inventory unavailable" : servers.length === 0 ? "Your next server starts here." : "No matching instances"}</strong>
                <span>{servers.length === 0 ? "Deploy a server or refresh to check your infrastructure." : "Try a different name, node, address, or status filter."}</span>
              </motion.div>
            )}
          </motion.div>
        </section>
      </div>
    </div>
  );
}

const ServerCard = ({ server, view, isBusy, onAction, onOpenTerminal }: any) => {
  if (view === 'list') {
    return (
      <motion.article initial={false} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="dashboard-server-card snx-server-row">
        <div className="snx-server-identity">
          <StatusPill status={server.status} />
          <div className="min-w-0"><h3>{server.name}</h3><p>{server.id}</p></div>
        </div>
        <div className="snx-server-facts">
          <div><span>Address</span><strong>{server.ip}</strong></div>
          <div><span>Runtime</span><strong>{server.type}</strong></div>
          <div className="snx-list-resources">
            <div className="snx-resource-ring-row"><PulseRing value={server.cpu} size={44} label={`${server.name} CPU`} /><span><small>CPU limit</small><b>{Math.round(server.cpu)}%</b></span></div>
            <div className="snx-resource-ring-row"><PulseRing value={server.ram ? 100 : 0} size={44} label={`${server.name} memory`} /><span><small>Allocated</small><b>{server.ram.toFixed(1)} GB</b></span></div>
          </div>
        </div>
        <div className="snx-server-actions"><ActionButtons status={server.status} isBusy={isBusy} onAction={onAction} /><ConsoleButton onOpenTerminal={onOpenTerminal} /></div>
      </motion.article>
    );
  }

  return (
    <motion.article initial={false} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="dashboard-server-card snx-server-card">
      <div className="snx-server-card-topline" aria-hidden="true" />
      <div className="snx-server-card-heading">
        <div className="min-w-0"><h3>{server.name}</h3><p className="snx-server-id">{server.id}</p></div>
        <StatusPill status={server.status} />
      </div>
      <div className="snx-server-address"><Globe className="h-3.5 w-3.5" /><span>{server.ip}</span><button type="button" aria-label={`Open ${server.name} console`} onClick={onOpenTerminal}><Terminal className="h-3.5 w-3.5" /></button></div>
      <div className="snx-server-type"><Shield className="h-3.5 w-3.5" /> {server.type}</div>
      <div className="snx-resource-stack">
        <div className="snx-resource-ring-row"><PulseRing value={server.cpu} size={58} label={`${server.name} CPU`} /><div><span><Cpu className="h-3.5 w-3.5" /> CPU limit</span><b>{Math.round(server.cpu)}%</b></div></div>
        <div className="snx-resource-ring-row"><PulseRing value={server.ram ? 100 : 0} size={58} label={`${server.name} memory`} /><div><span><HardDrive className="h-3.5 w-3.5" /> Allocated RAM</span><b>{server.ram.toFixed(1)} GB</b></div></div>
      </div>
      <div className="snx-server-card-footer"><span><Clock className="h-3.5 w-3.5" /> {server.node}</span><div className="snx-server-actions"><ActionButtons status={server.status} isBusy={isBusy} onAction={onAction} /><ConsoleButton onOpenTerminal={onOpenTerminal} /></div></div>
    </motion.article>
  );
};

const ConsoleButton = ({ onOpenTerminal }: { onOpenTerminal: () => void }) => (
  <button type="button" onClick={onOpenTerminal} className="snx-icon-button snx-icon-button--console" title="Open console" aria-label="Open console"><Terminal className="h-4 w-4" /></button>
);

const ActionButtons = ({ status, isBusy, onAction }: any) => {
  const isOnline = status === 'online';
  return isOnline ? (
    <>
      <button type="button" onClick={() => onAction('restart')} disabled={isBusy} className="snx-icon-button" title="Restart" aria-label="Restart"><RotateCw className={`h-4 w-4 ${isBusy ? 'animate-spin' : ''}`} /></button>
      <button type="button" onClick={() => onAction('stop')} disabled={isBusy} className="snx-icon-button snx-icon-button--danger" title="Stop" aria-label="Stop"><Square className="h-4 w-4" fill="currentColor" /></button>
    </>
  ) : (
    <button type="button" onClick={() => onAction('start')} disabled={isBusy} className="snx-start-button"><Play className={`h-3.5 w-3.5 ${isBusy ? 'animate-pulse' : ''}`} fill="currentColor" /> Start</button>
  );
};
