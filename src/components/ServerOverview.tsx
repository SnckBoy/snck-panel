import { useEffect, useRef, useState } from "react";
import axios from "axios";
import { Activity, ArrowDownToLine, ArrowUpFromLine, Cpu, HardDrive, MemoryStick, Server, Users, Wifi } from "lucide-react";
import PulseRing from "./PulseRing";
import { EMPTY_TELEMETRY, TelemetrySnapshot } from "../types/telemetry";
import { formatBytes, formatCpu, formatNetworkRate, normalizeTelemetry } from "../utils/telemetry";

const STALE_AFTER_MS = 15000;

function StatCard({ label, icon: Icon, color, value, detail, progress, status }: { label: string; icon: any; color: string; value: string; detail: string; progress: number | null; status: TelemetrySnapshot["status"] }) {
  return <article className="rounded-2xl border border-white/10 bg-black/20 p-4 backdrop-blur-xl"><div className="flex items-center justify-between gap-3"><div className="flex min-w-0 items-center gap-3"><PulseRing value={progress} size={54} strokeWidth={3} showValue={false} icon={<Icon className="h-4 w-4" />} label={label} status={status} /><div className="min-w-0"><p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">{label}</p><p className="mt-1 truncate font-mono text-lg font-semibold text-foreground">{value}</p><p className="mt-1 truncate text-xs text-muted-foreground">{detail}</p></div></div><span className="h-2 w-2 rounded-full" style={{ backgroundColor: color, boxShadow: `0 0 12px ${color}` }} /></div></article>;
}

export default function ServerOverview({ serverId, server }: { serverId: string; server: any }) {
  const [snapshot, setSnapshot] = useState<TelemetrySnapshot>(EMPTY_TELEMETRY);
  const latestRef = useRef(EMPTY_TELEMETRY);
  const [connected, setConnected] = useState(false);

  useEffect(() => {
    let mounted = true;
    let timer: number | undefined;
    let requestId = 0;
    let controller: AbortController | null = null;
    const pull = async () => {
      if (!mounted || document.visibilityState !== "visible") return;
      const currentRequest = ++requestId;
      controller?.abort();
      controller = new AbortController();
      try {
        const response = await axios.get(`/api/servers/${serverId}/stats`, { signal: controller.signal });
        if (!mounted || currentRequest !== requestId) return;
        const next = normalizeTelemetry(response.data, latestRef.current, Date.now());
        latestRef.current = next;
        setSnapshot(next);
        setConnected(next.status === "live" || next.status === "stale");
      } catch (error) {
        if (!mounted || axios.isCancel(error)) return;
        setConnected(false);
        latestRef.current = { ...latestRef.current, status: "unavailable" };
        setSnapshot(previous => ({ ...previous, status: "unavailable" }));
      }
      timer = window.setTimeout(() => void pull(), 3000);
    };
    void pull();
    const onVisibility = () => { if (document.visibilityState === "visible") void pull(); };
    document.addEventListener("visibilitychange", onVisibility);
    return () => { mounted = false; controller?.abort(); if (timer) window.clearTimeout(timer); document.removeEventListener("visibilitychange", onVisibility); };
  }, [serverId]);

  const status = snapshot.timestamp && Date.now() - snapshot.timestamp > STALE_AFTER_MS ? "stale" : snapshot.status;
  const statusText = status === "live" ? "Live" : status === "stale" ? "Stale telemetry" : "Telemetry unavailable";
  const serverAddress = "Connection details hidden";
  const statusClass = status === "live" ? "text-emerald-300" : status === "stale" ? "text-amber-300" : "text-rose-300";
  const waitingLabel = status === "unavailable" ? "Waiting for node data" : "No data reported";
  const memoryValue = snapshot.memory.usedBytes === null ? waitingLabel : formatBytes(snapshot.memory.usedBytes);
  const memoryDetail = snapshot.memory.limitBytes === null ? "Limit unavailable" : `of ${formatBytes(snapshot.memory.limitBytes)}`;
  const diskValue = snapshot.disk.usedBytes === null ? waitingLabel : formatBytes(snapshot.disk.usedBytes);
  const diskDetail = snapshot.disk.limitBytes === null ? "Limit unavailable" : `of ${formatBytes(snapshot.disk.limitBytes)}`;
  const cpuValue = snapshot.cpu.usagePercent === null ? waitingLabel : formatCpu(snapshot.cpu.usagePercent);
  const cpuDetail = snapshot.cpu.capacityPercent === null ? "Observed usage" : `of ${formatCpu(snapshot.cpu.capacityPercent)}`;
  const networkValue = snapshot.network.downloadBytesPerSecond === null ? waitingLabel : formatNetworkRate(snapshot.network.downloadBytesPerSecond);
  const networkDetail = snapshot.network.uploadBytesPerSecond === null ? "Upload unavailable" : `↑ ${formatNetworkRate(snapshot.network.uploadBytesPerSecond)}`;

  return <div className="h-full overflow-y-auto p-4 pb-10 md:p-6"><div className="mx-auto max-w-7xl space-y-5"><section className="rounded-3xl border border-white/10 bg-black/20 p-5 backdrop-blur-xl sm:p-7"><div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"><div><div className="mb-2 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-300"><Server className="h-3.5 w-3.5" />Server overview</div><h2 className="text-3xl font-semibold tracking-tight text-foreground">{server.name}</h2><div className="mt-3 flex flex-wrap items-center gap-2 text-sm text-muted-foreground"><span className={`inline-flex items-center gap-2 rounded-full border border-white/10 px-3 py-1 ${statusClass}`}><span className={`h-2 w-2 rounded-full ${status === "live" ? "bg-emerald-300" : status === "stale" ? "bg-amber-300" : "bg-rose-300"}`} />{server.status || "offline"} · {statusText}</span><span className="rounded-full border border-white/10 bg-white/5 px-3 py-1 font-mono text-xs">{serverAddress}</span></div></div><div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4"><div><p className="text-[10px] uppercase tracking-[0.16em] text-muted-foreground">Software</p><p className="mt-1 font-medium text-foreground">{server.type || "--"}</p></div><div><p className="text-[10px] uppercase tracking-[0.16em] text-muted-foreground">Version</p><p className="mt-1 font-mono text-foreground">{server.version || "--"}</p></div><div><p className="text-[10px] uppercase tracking-[0.16em] text-muted-foreground">Node</p><p className="mt-1 font-mono text-foreground">{server.nodeId || "local"}</p></div><div><p className="text-[10px] uppercase tracking-[0.16em] text-muted-foreground">Java</p><p className="mt-1 font-mono text-foreground">{server.javaVersion || "default"}</p></div></div></div></section>

<section><div className="mb-3 flex items-end justify-between"><div><p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">Resource usage</p><h3 className="mt-1 text-xl font-semibold text-foreground">Live capacity</h3></div><span className={`text-xs ${statusClass}`}>{connected ? "Sampling from node" : "Node offline"}</span></div><div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><StatCard label="CPU" icon={Cpu} color="#00F2FE" value={cpuValue} detail={cpuDetail} progress={snapshot.cpu.visualPercent} status={status} /><StatCard label="Memory" icon={MemoryStick} color="#9B51E0" value={memoryValue} detail={memoryDetail} progress={snapshot.memory.visualPercent} status={status} /><StatCard label="Disk" icon={HardDrive} color="#f6c453" value={diskValue} detail={diskDetail} progress={snapshot.disk.visualPercent} status={status} /><StatCard label="Network" icon={Wifi} color="#00FF87" value={networkValue} detail={networkDetail} progress={null} status={status} /></div></section>

<section className="grid gap-4 lg:grid-cols-[1.3fr_.7fr]"><article className="rounded-3xl border border-white/10 bg-black/20 p-5 backdrop-blur-xl"><div className="flex items-center justify-between"><div><p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">Players</p><h3 className="mt-1 text-xl font-semibold text-foreground">Player management</h3></div><Users className="h-5 w-5 text-cyan-300" /></div><div className="mt-5 grid grid-cols-2 gap-3"><div className="rounded-2xl border border-emerald-300/15 bg-emerald-300/[0.05] p-4"><p className="text-[10px] uppercase tracking-[0.16em] text-muted-foreground">Online</p><p className="mt-1 font-mono text-sm font-semibold text-emerald-200">Open Players tab</p></div><div className="rounded-2xl border border-white/10 bg-white/[0.03] p-4"><p className="text-[10px] uppercase tracking-[0.16em] text-muted-foreground">Maximum</p><p className="mt-1 font-mono text-sm font-semibold text-foreground">Reported in-game</p></div></div><p className="mt-4 text-sm text-muted-foreground">Open the dedicated Players tab to load the server’s real list response and manage players without mixing controls into the console.</p></article><article className="rounded-3xl border border-white/10 bg-black/20 p-5 backdrop-blur-xl"><div className="flex items-center gap-2"><Activity className="h-4 w-4 text-cyan-300" /><p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">Server information</p></div><dl className="mt-4 space-y-3 text-sm"><div className="flex justify-between gap-4"><dt className="text-muted-foreground">Server ID</dt><dd className="truncate font-mono text-foreground">{server.id || serverId}</dd></div><div className="flex justify-between gap-4"><dt className="text-muted-foreground">Port</dt><dd className="font-mono text-foreground">{server.port || "--"}</dd></div><div className="flex justify-between gap-4"><dt className="text-muted-foreground">Address</dt><dd className="truncate font-mono text-foreground">{serverAddress}</dd></div><div className="flex justify-between gap-4"><dt className="text-muted-foreground">Uptime</dt><dd className="font-mono text-foreground">{waitingLabel}</dd></div></dl></article></section></div></div>;
}
