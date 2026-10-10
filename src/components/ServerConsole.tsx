import React, { useCallback, useEffect, useMemo, useRef, useState } from "react";
import {
  Terminal as XTerm,
  Copy,
  Check,
  Trash2,
  ChevronDown,
  Wifi,
  Activity,
  Cpu,
  MemoryStick,
  HardDrive,
} from "lucide-react";
import { io, Socket } from "socket.io-client";
import { useAuth } from "../context/AuthContext";
import axios from "axios";
import { normalizeTelemetry, formatBytes, formatCpu } from "../utils/telemetry";

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
.qx-panel { background: #080808; border: 1px solid rgba(255,255,255,.045); border-radius: 4px; box-shadow: none; }
.qx-console-window-bar { background: #080808; border-bottom: 1px solid rgba(255,255,255,.055); }
.qx-console-toolbar { background: #080808; border-color: rgba(255,255,255,.055); }
.qx-console-body { background: #080808; }
.snx-server-workspace-card { background: #202020; border: 1px solid rgba(255,255,255,.025); border-radius: 4px; }
.snx-server-workspace-card-label { color: #b8b8b8; font-size: 12px; }
.snx-server-workspace-card-value { color: #f3f3f3; font-weight: 650; font-size: 15px; }
.snx-server-workspace-icon { display:grid;place-items:center;width:42px;height:42px;flex:0 0 auto;border-radius:9px;background:#171717;color:#e6e6e6; }
.snx-server-chart { min-width:0; background:#202020; border:1px solid rgba(255,255,255,.025); border-radius:4px; overflow:hidden; }
.snx-server-chart-grid { background-image:linear-gradient(to bottom, transparent calc(100% - 1px), rgba(255,255,255,.035) calc(100% - 1px));background-size:100% 33.333%; }
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
/* Classic Minecraft system terminal: restrained CRT glow and compact mobile controls. */
.qx-panel { background:#050807; border:1px solid #26352e; border-radius:8px; box-shadow:inset 0 0 0 1px rgba(55,255,150,.025),0 10px 32px rgba(0,0,0,.28); }
.qx-console-window-bar { background:linear-gradient(180deg,#101714,#090d0b); border-bottom:1px solid #26352e; min-height:48px; }
.qx-console-window-bar h1 { color:#cce8d5 !important; letter-spacing:.16em !important; }
.qx-console-toolbar { background:#090e0c; border-color:#1e2b24; }
.qx-console-body { background:#050907; background-image:linear-gradient(rgba(90,180,120,.018) 1px,transparent 1px); background-size:100% 4px; }
.qx-input-shell { background:#070c09; border:1px solid #293a30; border-radius:6px; }
.qx-input-shell:focus-within { border-color:rgba(68,220,125,.72); box-shadow:0 0 0 1px rgba(68,220,125,.12),0 0 16px rgba(35,170,85,.08); }
.qx-run { background:#12351f; color:#8df4ad; border:1px solid #276b3d; border-radius:6px; }
.qx-run:hover:not(:disabled) { background:#174629; border-color:#48b96d; }
.qx-window-control { display:grid; place-items:center; width:30px; height:30px; border:1px solid #26352e; background:#0a100c; color:#9bb9a4; border-radius:4px; }
.qx-window-control:hover { color:#a7ffbd; border-color:#3a754d; background:#102016; }
.qx-log-line:hover { background:rgba(70,180,100,.045) !important; }
.qx-console-floating { border:1px solid #3b6849 !important; box-shadow:0 0 0 1px rgba(58,160,85,.12),0 20px 70px #000 !important; }
.snx-connection-badge { border-radius:4px; border-color:rgba(68,220,125,.22); background:rgba(35,130,65,.08); }
.qx-scroll::-webkit-scrollbar-thumb { background:#263b2d; border-radius:3px; }
@media (min-width:1280px) { .snx-console-window { min-height:min(680px,calc(100vh - 250px)); } }
@media (max-width:767px) { .snx-console-window { height:min(64dvh,620px) !important; min-height:390px; border-radius:7px !important; } .snx-console-window-bar { padding:9px 10px !important; gap:6px; } .snx-console-title { flex:1; } .snx-console-toolbar { padding:7px 8px !important; gap:7px; } .snx-console-toolbar input { flex-basis:100%; min-width:0 !important; margin-left:0 !important; } .qx-console-body { padding:10px 8px !important; font-size:12px !important; line-height:1.55 !important; } .snx-command-bar { padding:8px !important; gap:7px; background:#080c09; border-top:1px solid #1d2a21; } .snx-command-input { padding:0 9px !important; border-radius:6px !important; } .snx-command-input input { padding-top:12px !important; padding-bottom:12px !important; font-size:12px !important; } .snx-execute-button { padding:0 12px !important; font-size:10px !important; letter-spacing:.08em !important; border-radius:6px !important; } .snx-server-workspace-card { border-radius:6px !important; } }
@media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation:none !important; transition:none !important; scroll-behavior:auto !important; } }
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

function ResourceStatus({ snapshot, server }: { snapshot: ReturnType<typeof normalizeTelemetry>; server?: ServerConsoleProps["server"] }) {
  const live = snapshot.status === "live";
  const statusLabel = live ? "Live node data" : snapshot.status === "stale" ? "Data is stale" : "Waiting for node data";
  const memoryValue = formatBytes(snapshot.memory.usedBytes);
  const memoryDetail = snapshot.memory.limitBytes === null ? "No memory limit reported" : memoryValue + " of " + formatBytes(snapshot.memory.limitBytes) + " used";
  const diskValue = formatBytes(snapshot.disk.usedBytes);
  const diskDetail = snapshot.disk.limitBytes === null ? "No disk limit reported" : diskValue + " of " + formatBytes(snapshot.disk.limitBytes) + " used";
  const address = server?.ipAlias ? String(server.ipAlias) + ":" + String(server.port ?? "") : server?.port ? window.location.hostname + ":" + String(server.port) : "Address unavailable";
  return <section className="flex flex-col gap-3">
    <article className="snx-server-workspace-card flex items-center gap-3 p-3"><div className="snx-server-workspace-icon"><Wifi size={22} /></div><div className="min-w-0"><p className="snx-server-workspace-card-label">Address (click to copy)</p><button type="button" onClick={() => { void navigator.clipboard?.writeText(address); }} className="snx-server-workspace-card-value mt-1 block max-w-full truncate text-left" title="Copy server address">{address}</button></div></article>
    <article className="snx-server-workspace-card flex items-center gap-3 p-3"><div className="snx-server-workspace-icon" style={{ background: live ? "#254c35" : "#5a2927" }}><Activity size={22} /></div><div className="min-w-0"><p className="snx-server-workspace-card-label">Server status</p><p className="snx-server-workspace-card-value mt-1 capitalize">{String(server?.status || (live ? "Online" : "Offline"))}</p></div></article>
    <article className="snx-server-workspace-card p-3"><div className="mb-2 flex items-center gap-3"><div className="snx-server-workspace-icon"><Cpu size={21} /></div><div className="min-w-0"><p className="snx-server-workspace-card-label">CPU Usage</p><p className="snx-server-workspace-card-value mt-1">{formatCpu(snapshot.cpu.usagePercent)}</p></div></div><div className="h-1 overflow-hidden rounded-full bg-white/[0.08]"><div className="h-full rounded-full bg-[#b99a45]" style={{ width: String(Math.max(0, Math.min(100, snapshot.cpu.visualPercent ?? 0))) + "%" }} /></div></article>
    <article className="snx-server-workspace-card p-3"><div className="mb-2 flex items-center gap-3"><div className="snx-server-workspace-icon"><MemoryStick size={21} /></div><div className="min-w-0"><p className="snx-server-workspace-card-label">RAM Usage</p><p className="snx-server-workspace-card-value mt-1">{memoryValue}</p></div></div><p className="text-[10px] text-white/45">{memoryDetail}</p><div className="mt-2 h-1 overflow-hidden rounded-full bg-white/[0.08]"><div className="h-full rounded-full bg-[#b99a45]" style={{ width: String(Math.max(0, Math.min(100, snapshot.memory.visualPercent ?? 0))) + "%" }} /></div></article>
    <article className="snx-server-workspace-card p-3"><div className="mb-2 flex items-center gap-3"><div className="snx-server-workspace-icon"><HardDrive size={21} /></div><div className="min-w-0"><p className="snx-server-workspace-card-label">Storage Usage</p><p className="snx-server-workspace-card-value mt-1">{diskValue}</p></div></div><p className="text-[10px] text-white/45">{diskDetail}</p><div className="mt-2 h-1 overflow-hidden rounded-full bg-white/[0.08]"><div className="h-full rounded-full bg-[#b99a45]" style={{ width: String(Math.max(0, Math.min(100, snapshot.disk.visualPercent ?? 0))) + "%" }} /></div></article>
    <article className="snx-server-workspace-card p-3"><div className="mb-2 flex items-center gap-2"><Activity size={16} /><p className="snx-server-workspace-card-label">Network Usage</p></div><div className="flex justify-between gap-3 font-mono text-[11px] text-white/80"><span>↓ {formatBytes(snapshot.network.downloadTotalBytes)}</span><span>↑ {formatBytes(snapshot.network.uploadTotalBytes)}</span></div><p className="mt-1 text-[10px] text-white/40">{statusLabel}</p></article>
  </section>;
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
  const [resourceHistory, setResourceHistory] = useState<Array<{ cpu: number | null; ram: number | null; network: number | null }>>([]);
  const lastActionNotice = useRef("");
  useEffect(() => {
    setResourceHistory((previous) => [...previous, {
      cpu: resourceSnapshot.cpu.visualPercent,
      ram: resourceSnapshot.memory.visualPercent,
      network: (() => {
        const download = resourceSnapshot.network.downloadTotalBytes;
        const upload = resourceSnapshot.network.uploadTotalBytes;
        return download == null || upload == null ? null : download + upload;
      })(),
    }].slice(-36));
  }, [resourceSnapshot]);
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
    // Action feedback belongs in the server controls, not in Minecraft stdout/stderr.
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
      // Keep transport/auth diagnostics out of the Minecraft log stream.
    });

    socket.on("log", (data: string) => {
      if (typeof data !== "string") return;
      const lines = data.split(/\r?\n/).filter(line => line.trim()).slice(-MAX_LOG_LINES).map(line => line.slice(0, 10000));
      pendingLogs.current = [...pendingLogs.current, ...lines].slice(-MAX_LOG_LINES);
    });

    socket.on("disconnect", (r: string) => {
      setConnected(false);
      // Connection state is shown by the live indicator; do not pollute Minecraft logs.
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
      // Commands are sent to Minecraft, while the log pane displays server output only.
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

    let text = "text-zinc-300";
    let rail = "bg-zinc-600/50";
    let badge = "LOG";
    let badgeClass = "text-zinc-400 bg-white/[0.03] border-white/[0.08]";

    if (level === "error") { text = "text-red-300"; rail = "bg-red-400/70"; badge = "ERROR"; badgeClass = "text-red-300 bg-red-400/[0.06] border-red-400/15"; }
    else if (level === "warn") { text = "text-zinc-200"; rail = "bg-zinc-400/70"; badge = "WARN"; badgeClass = "text-zinc-300 bg-white/[0.04] border-white/[0.1]"; }
    else if (log.startsWith(">")) { text = "text-zinc-100 font-medium"; rail = "bg-zinc-300/70"; badge = "CMD"; badgeClass = "text-zinc-200 bg-white/[0.06] border-white/[0.12]"; }
    else if (log.startsWith("[System")) { text = "text-zinc-400"; rail = "bg-zinc-500/60"; badge = "SYSTEM"; badgeClass = "text-zinc-400 bg-white/[0.03] border-white/[0.08]"; }
    else if (log.includes("INFO")) { text = "text-zinc-300"; rail = "bg-zinc-500/50"; }

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
        <div className="relative flex flex-col w-full max-w-[1600px] mx-auto min-h-full gap-3 md:gap-4 p-3 md:p-4 pb-8">
          
          {/* ═══════════ DEDICATED CONSOLE AREA ═══════════ */}
          <div className="flex min-w-0 flex-col xl:flex-row gap-3 md:gap-5">
          <div className="flex flex-1 flex-col gap-3 w-full xl:flex-1 min-w-0">
            <header className="flex flex-wrap items-center justify-between gap-3 px-1 py-1 md:px-0 md:py-0">
              <div className="min-w-0">
                
                <h1 className="mt-1 truncate text-base font-semibold text-slate-100">{server?.name || `Server ${serverId}`}</h1>
              </div>
              <span className={`inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] ${statusLabel === "Online" ? "border-emerald-400/25 bg-emerald-400/10 text-emerald-300" : statusLabel === "Starting" || statusLabel === "Stopping" ? "border-amber-400/25 bg-amber-400/10 text-amber-200" : "border-slate-400/20 bg-slate-400/10 text-slate-300"}`}>
                <span className={`h-1.5 w-1.5 rounded-full ${statusLabel === "Online" ? "bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,.8)]" : statusLabel === "Starting" || statusLabel === "Stopping" ? "bg-amber-400" : "bg-slate-500"}`} />
                {statusLabel}
              </span>
            </header>
            <section
              className={`snx-console-window flex flex-col h-[min(64vh,680px)] min-h-[390px] md:h-[58vh] xl:h-[calc(100vh-250px)] qx-panel rounded-xl overflow-hidden relative ${
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
                  {["bg-zinc-500", "bg-zinc-400", "bg-zinc-300"].map((c, i) => (
                    <span
                      key={i}
                      className={`w-2.5 h-2.5 sm:w-[11px] sm:h-[11px] rounded-full ${c} opacity-80 hover:opacity-100 transition-all cursor-default`}
                    />
                  ))}
                </div>

                <div className="snx-console-title flex items-center gap-2 min-w-0">
                  <XTerm size={13} className="text-zinc-400 shrink-0" />
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
                  {FILTERS.map((item) => <button key={item.key} type="button" onClick={() => setFilter(item.key)} className={`rounded-md px-2.5 py-1 text-[10px] font-medium transition-colors ${filter === item.key ? "bg-white/[0.08] text-zinc-100" : "text-zinc-500 hover:text-zinc-300"}`}>{item.label}</button>)}
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
                    <span className="text-zinc-400">❯</span>
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
                      className="text-zinc-400/70 text-xs select-none"
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
                  className="qx-tail-in absolute bottom-28 sm:bottom-32 right-4 sm:right-5 z-20 flex items-center gap-1.5 qx-display text-[9px] font-bold uppercase tracking-[0.14em] px-2.5 py-1.5 bg-zinc-950 text-zinc-200 border border-white/15 rounded-md hover:bg-white/[0.06] transition-colors"
                >
                  <ChevronDown size={11} />
                  Tail
                </button>
              )}

              {/* ── Command bar ── */}
              <form
                onSubmit={send}
                className="snx-command-bar qx-console-command p-2 sm:p-3 md:p-4 flex gap-2 relative z-10"
              >
                <div className="snx-command-input qx-input-shell flex-1 flex items-center rounded-xl px-2.5 sm:px-4 transition-all duration-300 min-w-0">
                  <span className="text-zinc-300 qx-mono text-xs mr-1.5 sm:mr-3 select-none font-semibold whitespace-nowrap shrink-0">
                    <span className="hidden sm:inline">server&gt;</span>
                    <span className="sm:hidden">&gt;</span>
                  </span>
                  <input
                    ref={inputRef}
                    type="text"
                    value={command}
                    onChange={(e) => setCommand(e.target.value)}
                    onKeyDown={onInputKey}
                    className="flex-1 bg-transparent py-2.5 sm:py-3 text-emerald-50/90 focus:outline-none qx-mono text-xs placeholder:text-foreground/25 caret-zinc-200 min-w-0"
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
          <aside className="w-full xl:w-[24%] xl:sticky xl:top-3 xl:self-start">
            <ResourceStatus snapshot={resourceSnapshot} server={server} />
          </aside>
          </div>
            <section className="grid grid-cols-1 gap-3 md:grid-cols-3">
              {([{ key: "cpu", title: "CPU Usage", values: resourceHistory.map((point) => point.cpu) }, { key: "ram", title: "RAM Usage", values: resourceHistory.map((point) => point.ram) }, { key: "network", title: "Network Usage", values: resourceHistory.map((point) => point.network) }] as const).map((chart) => {
                const valid = chart.values.filter((value): value is number => value !== null && Number.isFinite(value));
                const min = valid.length ? Math.min(...valid) : 0;
                const max = valid.length ? Math.max(...valid) : 1;
                const points = chart.values.map((value, index) => { const x = chart.values.length <= 1 ? 0 : (index / (chart.values.length - 1)) * 100; const y = value === null ? null : max === min ? 50 : 92 - ((value - min) / (max - min)) * 78; return y === null ? null : String(x) + "," + String(y); }).filter((point): point is string => point !== null).join(" ");
                return <article key={chart.key} className="snx-server-chart p-3 md:p-4">
                  <div className="mb-3 flex items-center justify-between gap-3"><h2 className="text-sm font-semibold text-white/80">{chart.title}</h2><Activity size={15} className="text-[#c5a84e]" /></div>
                  <p className="mb-2 text-[10px] text-white/45">{valid.length > 1 ? (chart.key === "network" ? "Traffic history · bytes" : "Observed utilization") : "Waiting for live samples"}</p>
                  <div className="snx-server-chart-grid h-24 overflow-hidden">{valid.length > 1 ? <svg viewBox="0 0 100 100" preserveAspectRatio="none" className="h-full w-full" aria-label={chart.title + " history"} role="img"><polyline points={points} fill="none" stroke="#b99a45" strokeWidth="1.5" vectorEffect="non-scaling-stroke" strokeLinejoin="round" strokeLinecap="round" /></svg> : <div className="flex h-full items-end"><div className="h-px w-full bg-[#b99a45]/60" /></div>}</div>
                  <div className="mt-2 flex justify-between text-[10px] text-white/40"><span>Older</span><span>Latest</span></div>
                </article>;
              })}
            </section>
        </div>
      </div>
    </>
  );
}
