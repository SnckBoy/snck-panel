import React, { useCallback, useEffect, useMemo, useRef, useState } from "react";
import {
  Terminal as XTerm,
  Copy,
  Check,
  Trash2,
  ChevronDown,

} from "lucide-react";
import { io, Socket } from "socket.io-client";
import { useAuth } from "../context/AuthContext";
import axios from "axios";
import { normalizeTelemetry, formatBytes, formatCpu, formatPercent } from "../utils/telemetry";

/* ═══════════════════════════════════════════════════════
   TYPES
═══════════════════════════════════════════════════════ */

interface ServerConsoleProps {
  serverId: string;
  server?: {
    version?: string;
    name?: string;
    status?: string;
    [key: string]: unknown;
  };
  actionNotice?: { tone: "info" | "success" | "error"; text: string } | null;
}

type LogLevel = "info" | "warn" | "error";
type LogFilter = "important" | "all";

/* ═══════════════════════════════════════════════════════
   CONSTANTS
═══════════════════════════════════════════════════════ */

const MAX_LOG_LINES = 200;
const STATS_POLL_MS = 5000;
const SPARK_CAP = 40;
const ANSI_RE = /\x1B(?:[@-Z\\-_]|\[[0-?]*[ -/]*[@-~])/g;

const FILTERS: { key: LogFilter; label: string }[] = [
  { key: "important", label: "Important Logs" },
  { key: "all", label: "All Logs" },
];

/* ═══════════════════════════════════════════════════════
   STYLES — typography, keyframes, ambient layers
═══════════════════════════════════════════════════════ */

const STYLES = `
::selection { background: rgba(99,102,241,.25); }
.qx-display { font-family: 'Inter', system-ui, sans-serif; }
.qx-mono { font-family: 'JetBrains Mono', ui-monospace, 'SF Mono', monospace; }
@keyframes qx-tail-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes qx-ping { 0% { transform: scale(1); opacity: .5; } 75%,100% { transform: scale(2); opacity: 0; } }
@keyframes qx-blink { 0%,49% { opacity:1; } 50%,100% { opacity:0; } }
.qx-enter-right, .qx-log-line { animation: none; }
.qx-tail-in { animation: qx-tail-in .18s ease both; }
.qx-panel { background: #0b1017; border: 1px solid rgba(148,163,184,.16); border-radius: 14px; box-shadow: 0 8px 28px rgba(0,0,0,.12); }
.qx-console-window-bar { background: #0d141d; border-bottom: 1px solid rgba(148,163,184,.12); }
.qx-console-body { background: #080d13; }
.qx-console-toolbar { background: #0b1017; border-color: rgba(148,163,184,.12); }
.snx-connection-badge { border: 1px solid rgba(148,163,184,.14); background: rgba(148,163,184,.05); }
.qx-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
.qx-scroll::-webkit-scrollbar-track { background: #09090b; }
.qx-scroll::-webkit-scrollbar-thumb { background: #2a2a31; border-radius: 4px; }
.qx-scroll::-webkit-scrollbar-thumb:hover { background: #3b3b45; }
.qx-run { transition: color .15s ease, background .15s ease, border-color .15s ease; }
.qx-input-shell:focus-within { border-color: rgba(99,102,241,.65); box-shadow: 0 0 0 1px rgba(99,102,241,.18); }
.qx-telemetry-row { transition: background .15s ease; }
.qx-telemetry-row:hover { background: rgba(255,255,255,.025); }
.qx-console-floating { left: 50%; top: 50%; max-width: calc(100vw - 24px); max-height: calc(100vh - 24px); border-radius: 10px !important; }
.qx-console-minimized { height: auto !important; min-height: 0 !important; }
.qx-console-minimized .qx-console-body, .qx-console-minimized .qx-console-command { display: none !important; }
@media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation: none !important; transition: none !important; scroll-behavior: auto !important; } }
`

/* ═══════════════════════════════════════════════════════
   HELPERS
═══════════════════════════════════════════════════════ */

const stripAnsi = (s: string) => s.replace(ANSI_RE, "");

const levelOf = (raw: string): LogLevel => {
  const l = stripAnsi(raw);
  if (/ERROR|Exception|FATAL/i.test(l)) return "error";
  if (/WARN|Can't keep up|behind/i.test(l)) return "warn";
  return "info";
};

const isImportantLog = (raw: string) => {
  const l = stripAnsi(raw).trim();
  // Default view is intentionally quiet: actionable events only.
  if (/^>/.test(l)) return true;
  if (/\b(ERROR|FATAL|SEVERE|EXCEPTION|WARN|WARNING)\b|Can't keep up|crash|failed|failure|timed out|timeout|out of memory/i.test(l)) return true;
  if (/joined the game|left the game|lost connection|logged in|logged out|server started|server stopped|saving worlds|saving players|stopping server|ready for connections/i.test(l)) return true;
  if (/^\[System Error\]/i.test(l)) return true;
  return false;
};

/* ═══════════════════════════════════════════════════════
   CORNER BRACKETS — rack-mount hardware detail
═══════════════════════════════════════════════════════ */

function Corners({ tone = "border-emerald-400/25" }: { tone?: string }) {
  const base = "pointer-events-none absolute w-3.5 h-3.5 z-10";
  return (
    <>
      <span className={`${base} -top-px -left-px border-t-2 border-l-2 ${tone}`} />
      <span className={`${base} -top-px -right-px border-t-2 border-r-2 ${tone}`} />
      <span className={`${base} -bottom-px -left-px border-b-2 border-l-2 ${tone}`} />
      <span className={`${base} -bottom-px -right-px border-b-2 border-r-2 ${tone}`} />
    </>
  );
}

/* ═══════════════════════════════════════════════════════
   CONNECTION PILL + CLOCK
═══════════════════════════════════════════════════════ */

function ConnPill({ live }: { live: boolean }) {
  return (
    <span className="snx-connection-badge flex items-center gap-2 px-3 py-1 rounded-sm">
      <span className="relative flex h-2 w-2">
        {live && (
          <span
            className="absolute inset-0 rounded-full bg-emerald-400"
            style={{ animation: "qx-ping 1.6s cubic-bezier(0,0,0.2,1) infinite" }}
          />
        )}
        <span
          className={`relative rounded-full h-2 w-2 transition-colors duration-500 ${
            live
              ? "bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.9)]"
              : "bg-red-400 shadow-[0_0_6px_rgba(248,113,113,0.9)]"
          }`}
        />
      </span>
      <span
        className={`qx-display text-[9px] font-bold uppercase tracking-[0.18em] transition-colors duration-500 ${
          live ? "text-emerald-400" : "text-red-400"
        }`}
      >
        {live ? "Live" : "Offline"}
      </span>
    </span>
  );
}

function Clock() {
  const [now, setNow] = useState(() => new Date());
  useEffect(() => {
    const iv = setInterval(() => setNow(new Date()), 1000);
    return () => clearInterval(iv);
  }, []);
  return (
    <span className="qx-mono text-[11px] text-slate-400 tabular-nums tracking-tight">
      {now.toLocaleTimeString("en-GB", { hour12: false })}
    </span>
  );
}

/* ═══════════════════════════════════════════════════════
   MAIN COMPONENT
═══════════════════════════════════════════════════════ */

function ResourceMetric({ label, value, detail, percent, tone }: { label: string; value: string; detail: string; percent: number | null; tone: string }) {
  return <div className="border-b border-white/[0.07] py-3 last:border-b-0"><div className="mb-1.5 flex items-baseline justify-between gap-3"><p className="qx-display text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">{label}</p><p className="qx-mono text-xs font-medium text-slate-200">{value}</p></div><div className="h-1.5 overflow-hidden rounded-full bg-white/[0.08]"><div className="h-full rounded-full transition-[width] duration-500" style={{ width: percent === null ? "0%" : `${Math.max(0, Math.min(100, percent))}%`, backgroundColor: tone }} /></div><p className="mt-1.5 text-[10px] text-slate-500">{detail}</p></div>;
}

function ResourceStatus({ snapshot }: { snapshot: ReturnType<typeof normalizeTelemetry> }) {
  const live = snapshot.status === "live";
  const statusLabel = live ? "Live node data" : snapshot.status === "stale" ? "Data is stale" : "Waiting for node data";
  const memoryValue = formatBytes(snapshot.memory.usedBytes);
  const memoryDetail = snapshot.memory.limitBytes === null ? "No memory limit reported" : `${memoryValue} of ${formatBytes(snapshot.memory.limitBytes)} used`;
  const diskValue = formatBytes(snapshot.disk.usedBytes);
  const diskDetail = snapshot.disk.limitBytes === null ? "No disk limit reported" : `${diskValue} of ${formatBytes(snapshot.disk.limitBytes)} used`;
  return <section className="qx-panel p-4 sm:p-5"><div className="mb-3 flex items-center justify-between gap-3"><h2 className="qx-display text-xs font-semibold uppercase tracking-[0.16em] text-slate-300">Resource usage</h2><span className={`text-[10px] ${live ? "text-emerald-300" : "text-slate-500"}`}>{statusLabel}</span></div><ResourceMetric label="CPU" value={formatCpu(snapshot.cpu.usagePercent)} detail={snapshot.cpu.capacityPercent === null ? "Waiting for capacity" : `Capacity ${formatPercent(snapshot.cpu.capacityPercent)}`} percent={snapshot.cpu.visualPercent} tone="#818cf8" /><ResourceMetric label="RAM" value={memoryValue} detail={memoryDetail} percent={snapshot.memory.visualPercent} tone="#818cf8" /><ResourceMetric label="Disk" value={diskValue} detail={diskDetail} percent={snapshot.disk.visualPercent} tone="#818cf8" /><div className="pt-3"><div className="mb-2 flex items-center justify-between"><p className="qx-display text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Network</p><span className="text-[10px] text-slate-500">live totals</span></div><div className="flex justify-between gap-3 font-mono text-[11px] text-slate-300"><span>↓ {formatBytes(snapshot.network.downloadTotalBytes)}</span><span>↑ {formatBytes(snapshot.network.uploadTotalBytes)}</span></div></div></section>;
}

export default function ServerConsole({ serverId, server, actionNotice }: ServerConsoleProps) {
  const [logs, setLogs] = useState<string[]>([]);
  const [sending, setSending] = useState(false);
  const sendingRef = useRef(false);
  const pendingLogs = useRef<string[]>([]);
  const [command, setCommand] = useState("");
  const [cmdHistory, setCmdHistory] = useState<string[]>([]);
  const [histIdx, setHistIdx] = useState(-1);
  const [connected, setConnected] = useState(false);
  const [accessDenied, setAccessDenied] = useState("");
  const [ready, setReady] = useState(false);
  const [filter, setFilter] = useState<LogFilter>("important");
  const [search, setSearch] = useState("");
  const [atBottom, setAtBottom] = useState(true);
  const [copied, setCopied] = useState(false);
  const [isFloating, setIsFloating] = useState(false);
  const [isMinimized, setIsMinimized] = useState(false);
  const [wrapLines, setWrapLines] = useState(true);
  const [terminalFontSize, setTerminalFontSize] = useState<"small" | "normal" | "large">("normal");
  const [resourceSnapshot, setResourceSnapshot] = useState(() => normalizeTelemetry(null));
  const lastActionNotice = useRef("");
  const [windowOffset, setWindowOffset] = useState({ x: 0, y: 0 });
  const dragRef = useRef<{ startX: number; startY: number; originX: number; originY: number } | null>(null);

  const bodyRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);
  const sockRef = useRef<Socket | null>(null);
  const { token } = useAuth();
  const serverStatus = String(server?.status || "unknown").toLowerCase();
  const serverOffline = ["offline", "stopped", "unknown"].includes(serverStatus);
  const statusLabel = serverStatus === "running" || serverStatus === "online" ? "Online" : serverStatus === "starting" ? "Starting" : serverStatus === "stopping" ? "Stopping" : "Offline";

  useEffect(() => {
    const t = setTimeout(() => setReady(true), 60);
    return () => clearTimeout(t);
  }, []);

  useEffect(() => {
    lastActionNotice.current = "";
  }, [serverId]);

  useEffect(() => {
    if (!actionNotice?.text || actionNotice.text === lastActionNotice.current) return;
    lastActionNotice.current = actionNotice.text;
    setLogs((previous) => [...previous, `[System] ${actionNotice.text}`].slice(-MAX_LOG_LINES));
  }, [actionNotice]);

  useEffect(() => {
    let mounted = true;
    let inFlight = false;
    const controller = new AbortController();
    setResourceSnapshot(normalizeTelemetry(null));
    const loadResources = async () => {
      if (inFlight || document.hidden) return;
      inFlight = true;
      try {
        const response = await axios.get(`/api/servers/${serverId}/stats`, { signal: controller.signal, timeout: 20000 });
        if (mounted) setResourceSnapshot(previous => normalizeTelemetry(response.data, previous, Date.now()));
      } catch {
        if (mounted) setResourceSnapshot(previous => normalizeTelemetry({ available: false }, previous, Date.now()));
      } finally { inFlight = false; }
    };
    void loadResources();
    const timer = window.setInterval(loadResources, STATS_POLL_MS);
    document.addEventListener("visibilitychange", loadResources);
    return () => { mounted = false; controller.abort(); window.clearInterval(timer); document.removeEventListener("visibilitychange", loadResources); };
  }, [serverId]);

  /* ── Socket stream ── */
  useEffect(() => {
    setLogs([]);
    pendingLogs.current = [];
    setCommand("");
    setCmdHistory([]);
    setHistIdx(-1);
    setAccessDenied("");
    setConnected(false);
    setAtBottom(true);
    if (!token || !serverId) return;
    // Batch bursts instead of rerendering on every socket packet.
    const flush = window.setInterval(() => {
      if (!pendingLogs.current.length) return;
      const lines = pendingLogs.current;
      pendingLogs.current = [];
      setLogs(previous => [...previous, ...lines].slice(-MAX_LOG_LINES));
    }, 100);
    const socket: Socket = io({
      auth: { token },
      transports: ["websocket", "polling"],
      reconnectionAttempts: 5,
      reconnectionDelay: 2000,
    });
    sockRef.current = socket;

    socket.on("connect", () => {
      setAccessDenied("");
      socket.emit("joinServer", serverId);
    });

    socket.on("server_joined", () => {
      setConnected(true);
    });

    socket.on("server_access_denied", (payload: { error?: string }) => {
      const message = payload?.error || "You are not authorized to view this server console.";
      setConnected(false);
      setAccessDenied(message);
      setLogs((p) => [...p, `[System Error] ${message}`].slice(-MAX_LOG_LINES));
    });

    socket.on("log", (data: string) => {
      if (typeof data !== "string") return;
      const lines = data.split(/\r?\n/).filter(line => line.trim()).slice(-MAX_LOG_LINES).map(line => line.slice(0, 10000));
      pendingLogs.current = [...pendingLogs.current, ...lines].slice(-MAX_LOG_LINES);
    });

    socket.on("disconnect", (r: string) => {
      setConnected(false);
      setLogs((p) => [...p, `[System] Disconnected. (${r})`].slice(-MAX_LOG_LINES));
    });

    socket.on("clear_logs", () => {
      pendingLogs.current = [];
      setLogs([]);
    });

    socket.on("connect_error", (e: Error & { description?: unknown; context?: unknown; data?: { message?: string } }) => {
      setConnected(false);
      const detail = [
        e.message,
        e.data?.message,
        typeof e.description === "string" ? e.description : "",
        typeof e.context === "string" ? e.context : "",
      ].filter(Boolean).join(" — ");
      setAccessDenied(`Console connection failed: ${detail || "unknown connection error"}`);
    });

    return () => {
      window.clearInterval(flush);
      pendingLogs.current = [];
      socket.emit("leaveServer", serverId);
      socket.removeAllListeners();
      socket.disconnect();
      sockRef.current = null;
    };
  }, [serverId, token]);

  /* ── Auto-scroll (respects user scroll position) ── */
  useEffect(() => {
    if (atBottom && bodyRef.current) {
      bodyRef.current.scrollTop = bodyRef.current.scrollHeight;
    }
  }, [logs, atBottom]);

  const onScroll = useCallback(() => {
    const el = bodyRef.current;
    if (!el) return;
    const d = el.scrollHeight - el.scrollTop - el.clientHeight;
    const near = d < 48;
    setAtBottom((prev) => (prev === near ? prev : near));
  }, []);

  const jumpToBottom = useCallback(() => {
    bodyRef.current?.scrollTo({ top: bodyRef.current.scrollHeight, behavior: "auto" });
    setAtBottom(true);
  }, []);

  const clearLogs = useCallback(() => {
    pendingLogs.current = [];
    setLogs([]);
    setAtBottom(true);
  }, []);

  const startDrag = useCallback((event: React.PointerEvent<HTMLElement>) => {
    if (!isFloating || (event.target as HTMLElement).closest("button")) return;
    event.preventDefault();
    dragRef.current = {
      startX: event.clientX,
      startY: event.clientY,
      originX: windowOffset.x,
      originY: windowOffset.y,
    };
    const onMove = (move: PointerEvent) => {
      if (!dragRef.current) return;
      setWindowOffset({
        x: dragRef.current.originX + move.clientX - dragRef.current.startX,
        y: dragRef.current.originY + move.clientY - dragRef.current.startY,
      });
    };
    const onUp = () => {
      dragRef.current = null;
      document.removeEventListener("pointermove", onMove);
      document.removeEventListener("pointerup", onUp);
    };
    document.addEventListener("pointermove", onMove);
    document.addEventListener("pointerup", onUp, { once: true });
  }, [isFloating, windowOffset]);

  /* ── "/" focuses the command line ── */
  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "/" && !e.ctrlKey && !e.metaKey && !(e.target as HTMLElement).closest("input, textarea, select, [contenteditable]")) {
        e.preventDefault();
        inputRef.current?.focus();
      }
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);

  /* ── Command submit ── */
  const send = useCallback(
    async (e: React.FormEvent) => {
      e.preventDefault();
      const cmd = command.trim();
      if (!cmd || serverOffline || accessDenied || sendingRef.current) return;
      sendingRef.current = true;
      setSending(true);
      setCommand("");
      setCmdHistory((h) => [cmd, ...h].slice(0, 50));
      setHistIdx(-1);
      // Echo locally for immediate feedback
      setLogs((p) => {
        const next = [...p, `> ${cmd}`];
        return next.length > MAX_LOG_LINES ? next.slice(-MAX_LOG_LINES) : next;
      });
      try {
        await axios.post(`/api/servers/${serverId}/command`, { command: cmd }, { timeout: 20000 });
      } catch (err: any) {
        setLogs((p) => {
          const next = [...p, `[System Error] Failed to send command: ${err.response?.data?.error || err.message}`];
          return next.length > MAX_LOG_LINES ? next.slice(-MAX_LOG_LINES) : next;
        });
        setCommand(current => current || cmd);
      } finally {
        sendingRef.current = false;
        setSending(false);
      }
    },
    [command, serverId, serverOffline, accessDenied]
  );

  /* ── Command history: ↑ / ↓ ── */
  const onInputKey = useCallback(
    (e: React.KeyboardEvent<HTMLInputElement>) => {
      if (e.key === "ArrowUp") {
        e.preventDefault();
        setHistIdx((i) => {
          const next = Math.min(i + 1, cmdHistory.length - 1);
          if (cmdHistory[next]) setCommand(cmdHistory[next]);
          return next;
        });
      } else if (e.key === "ArrowDown") {
        e.preventDefault();
        setHistIdx((i) => {
          const next = i - 1;
          if (next < 0) { setCommand(""); return -1; }
          setCommand(cmdHistory[next]);
          return next;
        });
      }
    },
    [cmdHistory]
  );

  /* ── Copy + clear ── */
  const copyLogs = useCallback(async () => {
    try {
      const text = logs.join("\n");
      if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(text);
      } else {
        const textarea = document.createElement("textarea");
        textarea.value = text;
        textarea.style.position = "fixed";
        textarea.style.opacity = "0";
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand("copy");
        textarea.remove();
      }
      setCopied(true);
      setTimeout(() => setCopied(false), 1500);
    } catch { /* clipboard unavailable */ }
  }, [logs]);

  /* ── Log line renderer ── */
  const renderLine = useCallback((raw: string): React.ReactNode => {
    const log = stripAnsi(raw);
    const ts = log.match(/^(\[\d{2}:\d{2}:\d{2}\s[^\]]+\]|\d{2}:\d{2}:\d{2})/);
    const level = levelOf(raw);

    let text = "text-slate-300";
    let rail = "bg-slate-600/40";
    let badge = "INFO";
    let badgeClass = "text-sky-300 bg-sky-400/10 border-sky-400/20";

    if (level === "error") { text = "text-rose-300 font-medium"; rail = "bg-rose-500/70"; badge = "ERROR"; badgeClass = "text-rose-300 bg-rose-400/10 border-rose-400/25"; }
    else if (level === "warn") { text = "text-amber-200"; rail = "bg-amber-400/70"; badge = "WARN"; badgeClass = "text-amber-200 bg-amber-400/10 border-amber-400/25"; }
    else if (log.startsWith(">")) { text = "text-emerald-300 font-semibold"; rail = "bg-emerald-400/70"; badge = "CMD"; badgeClass = "text-emerald-300 bg-emerald-400/10 border-emerald-400/25"; }
    else if (log.startsWith("[System")) { text = "text-violet-200/90 italic"; rail = "bg-violet-400/60"; badge = "SYSTEM"; badgeClass = "text-violet-200 bg-violet-400/10 border-violet-400/25"; }
    else if (log.includes("INFO")) { text = "text-sky-200/90"; rail = "bg-sky-500/50"; }

    const lineSize = terminalFontSize === "small" ? "text-[10px]" : terminalFontSize === "large" ? "text-sm" : "text-[11px] sm:text-xs";
    return (
      <span className={`flex-1 flex items-stretch min-w-0`}>
        <span className={`w-[2px] sm:w-[3px] shrink-0 rounded-full mr-2 sm:mr-3 self-stretch ${rail}`} />
          <span className={`${wrapLines ? "break-words whitespace-pre-wrap" : "whitespace-pre"} min-w-0 ${lineSize} leading-[1.6] ${text}`}>
          {ts && <span className="text-foreground/35 mr-1.5 sm:mr-2 select-none font-mono text-[10px]">{ts[0]}</span>}
          <span className={`mr-2 inline-flex rounded border px-1.5 py-0.5 align-middle text-[9px] font-bold leading-none tracking-[0.12em] ${badgeClass}`}>{badge}</span>
          {ts ? log.substring(ts[0].length).replace(/^\s*[:\-]\s*/, "") : log}
        </span>
      </span>
    );
  }, [wrapLines, terminalFontSize]);

  /* ── Derived ── */
  const counts = useMemo(() => {
    const c = { all: logs.length, info: 0, warn: 0, error: 0 };
    for (const l of logs) c[levelOf(l)]++;
    return c;
  }, [logs]);

  const visible = useMemo(
    () =>
      logs
        .map((l, i) => ({ l, i }))
        .filter(({ l }) => (filter === "all" || isImportantLog(l)) && (!search.trim() || stripAnsi(l).toLowerCase().includes(search.trim().toLowerCase()))),
    [logs, filter, search]
  );

  /* ═══════════════════════ RENDER ═══════════════════════ */
  return (
    <>
      <style>{STYLES}</style>
      <div className="absolute inset-0 overflow-y-auto text-foreground touch-auto overscroll-y-auto qx-scroll bg-transparent">
        <div className="relative flex flex-col xl:flex-row w-full max-w-[1440px] mx-auto min-h-full gap-3 md:gap-5 p-3 md:p-6 pb-20 md:pb-10">
          
          {/* ═══════════ DEDICATED CONSOLE AREA ═══════════ */}
          <div className="flex flex-1 flex-col gap-4 w-full xl:w-[76%] min-w-0">
            <header className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/[0.08] bg-black/25 px-4 py-3 md:px-5">
              <div className="min-w-0">
                <p className="qx-display text-[9px] font-bold uppercase tracking-[0.2em] text-slate-500">Server console</p>
                <h1 className="mt-1 truncate text-base font-semibold text-slate-100">{server?.name || `Server ${serverId}`}</h1>
              </div>
              <span className={`inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] ${statusLabel === "Online" ? "border-emerald-400/25 bg-emerald-400/10 text-emerald-300" : statusLabel === "Starting" || statusLabel === "Stopping" ? "border-amber-400/25 bg-amber-400/10 text-amber-200" : "border-slate-400/20 bg-slate-400/10 text-slate-300"}`}>
                <span className={`h-1.5 w-1.5 rounded-full ${statusLabel === "Online" ? "bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,.8)]" : statusLabel === "Starting" || statusLabel === "Stopping" ? "bg-amber-400" : "bg-slate-500"}`} />
                {statusLabel}
              </span>
            </header>
            <section
              className={`snx-console-window flex flex-col h-[520px] xs:h-[580px] md:h-[68vh] xl:h-[calc(100vh-120px)] qx-panel rounded-xl overflow-hidden relative ${
                ready ? "qx-enter-right" : "opacity-0"
              } ${isFloating ? "qx-console-floating fixed z-[60] w-[min(92vw,980px)]" : ""} ${isMinimized ? "qx-console-minimized" : ""}`}
              style={{
                animationDelay: "80ms",
                boxShadow: "none",
                transform: isFloating ? `translate3d(calc(-50% + ${windowOffset.x}px), calc(-50% + ${windowOffset.y}px), 0)` : undefined,
              }}
            >
              {/* ── Header ── */}
              <header className="snx-console-window-bar qx-window-drag-handle px-3 md:px-5 py-2.5 sm:py-3 flex items-center justify-between gap-2 relative z-10 cursor-default select-none" onPointerDown={startDrag}>
                <div className="flex items-center gap-[7px] shrink-0">
                  {["bg-[#ff5f57]", "bg-[#febc2e]", "bg-[#28c840]"].map((c, i) => (
                    <span
                      key={i}
                      className={`w-2.5 h-2.5 sm:w-[11px] sm:h-[11px] rounded-full ${c} opacity-80 hover:opacity-100 transition-all cursor-default`}
                    />
                  ))}
                </div>

                <div className="snx-console-title flex items-center gap-2 min-w-0">
                  <XTerm size={13} className="text-emerald-400/80 shrink-0" />
                  <div className="min-w-0 text-center">
                    <h1 className="qx-display text-[10px] sm:text-[11px] font-bold tracking-[0.2em] sm:tracking-[0.3em] text-slate-200 uppercase truncate">
                      System Console
                    </h1>
                    <p className="qx-mono text-[8px] sm:text-[9px] text-slate-500 truncate">
                      stream :: {serverId}
                    </p>
                  </div>
                </div>

                <div className="flex items-center gap-1 sm:gap-2 shrink-0">
                  <span className="hidden lg:block"><Clock /></span>
                  <ConnPill live={connected} />
                  {accessDenied && <span role="alert" className="max-w-[180px] truncate text-[9px] font-medium text-rose-300" title={accessDenied}>Access denied</span>}
                  <div className="flex items-center gap-1 ml-1 pl-1 border-l border-white/10">
                    <button type="button" className="qx-window-control" onClick={clearLogs} title="Clear console" aria-label="Clear console"><Trash2 size={12} /></button>
                    <button type="button" className="qx-window-control" onClick={() => void copyLogs()} title={copied ? "Copied" : "Copy logs"} aria-label={copied ? "Logs copied" : "Copy logs"}>{copied ? <Check size={12} /> : <Copy size={12} />}</button>

                  </div>
                </div>
              </header>

              <div className="qx-console-toolbar flex flex-wrap items-center gap-2 border-y px-3 py-2">
                <div className="flex items-center gap-1 rounded-lg border border-white/[0.07] bg-black/20 p-0.5">
                  {FILTERS.map((item) => <button key={item.key} type="button" onClick={() => setFilter(item.key)} className={`rounded-md px-2.5 py-1 text-[10px] font-medium transition-colors ${filter === item.key ? "bg-indigo-500/15 text-indigo-200" : "text-slate-500 hover:text-slate-300"}`}>{item.label}</button>)}
                </div>
                <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Filter output…" aria-label="Filter console output" className="ml-auto min-w-[150px] flex-1 rounded-md border border-white/[0.07] bg-black/20 px-2.5 py-1.5 font-mono text-[11px] text-slate-200 outline-none placeholder:text-slate-600 focus:border-indigo-400/40 sm:flex-none" />
              </div>

              {/* ── Log body ── */}
              <div
                ref={bodyRef}
                onScroll={onScroll}
                className={`qx-console-body flex-1 overflow-y-auto px-2.5 sm:px-4 md:px-5 py-3 sm:py-4 qx-mono ${terminalFontSize === "small" ? "text-[10px]" : terminalFontSize === "large" ? "text-sm" : "text-[11px] md:text-xs"} leading-[1.7] qx-scroll relative z-10`}
                style={{ WebkitOverflowScrolling: "touch" }}
                role="log"
                aria-live="polite"
                aria-label="Server console output"
              >
                {logs.length === 0 && (
                  <div className="flex items-center gap-2 text-foreground/25 py-2 text-xs">
                    <span className="text-emerald-400/70">❯</span>
                    <span>Awaiting connection</span>
                    <span className="flex gap-[3px] ml-1">
                      {[0, 1, 2].map((i) => (
                        <span
                          key={i}
                          className="w-[4px] h-[4px] rounded-full bg-emerald-400/60 inline-block"
                          style={{
                            animation: "qx-dot-bounce 1.4s ease-in-out infinite",
                            animationDelay: `${i * 0.18}s`,
                          }}
                        />
                      ))}
                    </span>
                  </div>
                )}

                {logs.length > 0 && visible.length === 0 && (
                  <div className="text-foreground/25 py-2 italic text-xs">
                    No “{filter}” lines in buffer.
                  </div>
                )}

                {visible.map(({ l, i }) => (
                  <div
                    key={`${i}-${l}`}
                    className="qx-log-line flex items-start py-[2px] sm:py-[3px] px-1 sm:px-2 -mx-1 sm:-mx-2 rounded-sm hover:bg-muted transition-colors duration-150 group"
                    style={{ animationDelay: `${Math.min(i * 10, 200)}ms` }}
                  >
                    <span className="hidden sm:inline-block text-foreground/[0.12] group-hover:text-emerald-300/50 mr-2 sm:mr-3 select-none shrink-0 w-7 sm:w-9 text-right text-[10px] leading-[1.75] transition-colors duration-200 tabular-nums">
                      {i + 1}
                    </span>
                    {renderLine(l)}
                  </div>
                ))}

                {visible.length > 0 && (
                  <div className="flex items-center py-[2px] sm:py-[3px] px-1 sm:px-2 -mx-1 sm:-mx-2">
                    <span className="hidden sm:inline-block w-7 sm:w-9 mr-2 sm:mr-3 shrink-0" />
                    <span
                      className="text-emerald-400/50 text-xs select-none"
                      style={{ animation: "qx-blink 1.1s step-end infinite" }}
                    >
                      ▋
                    </span>
                  </div>
                )}
              </div>

              {/* ── Jump-to-tail ── */}
              {!atBottom && logs.length > 0 && (
                <button
                  type="button"
                  onClick={jumpToBottom}
                  className="qx-tail-in absolute bottom-28 sm:bottom-32 right-4 sm:right-5 z-20 flex items-center gap-1.5 qx-display text-[9px] font-bold uppercase tracking-[0.14em] px-2.5 py-1.5 bg-black/80 backdrop-blur-md text-emerald-300 border border-emerald-400/30 rounded-lg shadow-[0_4px_20px_-4px_rgba(52,211,153,0.4)] hover:bg-emerald-400/10 transition-colors"
                >
                  <ChevronDown size={11} className="animate-bounce" />
                  Tail
                </button>
              )}

              {/* ── Command bar ── */}
              <form
                onSubmit={send}
                className="snx-command-bar qx-console-command p-2 sm:p-3 md:p-4 flex gap-2 relative z-10"
              >
                <div className="snx-command-input qx-input-shell flex-1 flex items-center rounded-xl px-2.5 sm:px-4 transition-all duration-300 min-w-0">
                  <span className="text-emerald-400/80 qx-mono text-xs mr-1.5 sm:mr-3 select-none font-semibold whitespace-nowrap shrink-0">
                    <span className="hidden sm:inline">server&gt;</span>
                    <span className="sm:hidden">&gt;</span>
                  </span>
                  <input
                    ref={inputRef}
                    type="text"
                    value={command}
                    onChange={(e) => setCommand(e.target.value)}
                    onKeyDown={onInputKey}
                    className="flex-1 bg-transparent py-2.5 sm:py-3 text-emerald-50/90 focus:outline-none qx-mono text-xs placeholder:text-foreground/25 caret-emerald-400 min-w-0"
                    placeholder="Type a command…"
                    spellCheck={false}
                    autoComplete="off"
                    aria-label="Server command input"
                  />
                  {command && (
                    <kbd className="hidden md:inline-block qx-mono text-[9px] text-foreground/20 border border-border rounded-sm px-1.5 py-0.5 ml-2 select-none shrink-0">
                      ↵
                    </kbd>
                  )}
                </div>

                <button
                  type="submit"
                                      disabled={!command.trim() || serverOffline || Boolean(accessDenied) || sending}
                    title={serverOffline ? "Start the server before sending commands" : "Execute command"}
                    className="snx-execute-button qx-run qx-display px-3.5 sm:px-6 md:px-7 py-2.5 sm:py-3 text-[11px] font-bold uppercase tracking-[0.14em] rounded-xl disabled:opacity-30 disabled:pointer-events-none shrink-0"
                >
                  {sending ? "Sending…" : "Execute"}
                </button>
              </form>
              {serverOffline && <p className="px-3 pb-3 text-[11px] text-amber-200/70">The server is offline. Start it before sending console commands.</p>}
            </section>
          </div>
          <aside className="w-full xl:w-[24%] xl:sticky xl:top-6 xl:self-start">
            <ResourceStatus snapshot={resourceSnapshot} />
          </aside>
        </div>
      </div>
    </>
  );
}
