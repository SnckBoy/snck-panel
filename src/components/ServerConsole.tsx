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
const ANSI_RE = /\x1B(?:\[[0-?]*[ -/]*[@-~]|\][^\x07]*(?:\x07|\x1B\\)|[@-_])/g;

const FILTERS: { key: LogFilter; label: string }[] = [
  { key: "important", label: "Important Only" },
  { key: "all", label: "Minecraft Logs" },
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
.qx-console-body { background-color:#050907; background-image:linear-gradient(rgba(3,8,4,.76),rgba(3,8,4,.82)),url("https://d2ol7oe51mr4n9.cloudfront.net/user_3KAQ4RgFOhcvBXL70JU7hK1JU98/172c3ad1-e18a-4d47-822f-233c799db9dc.jpg"); background-size:cover,cover; background-position:center,center; background-repeat:no-repeat; background-attachment:local; }
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

const stripAnsi = (s: string) =>
  s.replace(ANSI_RE, "").replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, "");

const normalizeMinecraftLogLine = (raw: string): string => {
  // Keep the familiar Minecraft timestamp/level format, while removing terminal
  // escape codes and wrappers added by Docker or the panel transport.
  let line = stripAnsi(raw).replace(/\uFEFF/g, "").trim();
  const segments = line.split(/\r/).map(part => part.trim()).filter(Boolean);
  line = segments.length ? segments[segments.length - 1] : "";
  if (!line) return "";

  // Keep the actual Minecraft payload when a transport prepends "LOG =>",
  // stdout/stderr, or other wrapper text before the native log prefix.
  // Locate the timestamp itself rather than assuming a particular loader format:
  // Vanilla, Paper, and Fabric use slightly different level/thread prefixes.
  const minecraftPrefix = /\[\d{2}:\d{2}:\d{2}(?:\.\d{1,3})?\]/;
  const prefixMatch = line.match(minecraftPrefix);
  if (prefixMatch && prefixMatch.index !== undefined && prefixMatch.index > 0) {
    line = line.slice(prefixMatch.index);
  } else {
    line = line.replace(/^\s*(?:LOG\s*(?:=>|→)\s*|(?:stdout|stderr)\s*:\s*)/i, "");
  }
  return line.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, "").trim();
};

const isMinecraftLogLine = (raw: string): boolean => {
  const line = normalizeMinecraftLogLine(raw);
  if (!line) return false;
  // Exclude only known panel/runtime diagnostics; preserve server, loader and JVM output.
  if (/^(?:LOG\s*(?:=>|→)|\[System\]|\[CMD\]|SNCK PANEL running|Attach error|Socket server join failed|UNCAUGHT EXCEPTION|UNHANDLED REJECTION|Docker (?:is unavailable|is installed)|Console connection failed|Console unavailable;|You are not authorized)/i.test(line)) return false;
  return true;
};

const levelOf = (raw: string): LogLevel => {
  const l = stripAnsi(normalizeMinecraftLogLine(raw));
  if (/\b(ERROR|Exception|FATAL|SEVERE)\b/i.test(l)) return "error";
  if (/\bWARN(?:ING)?\b|Can't keep up|behind/i.test(l)) return "warn";
  return "info";
};

const isRoutineJvmWarning = (raw: string) => {
  const l = stripAnsi(normalizeMinecraftLogLine(raw));
  return /sun\.misc\.Unsafe|MemUtil\$MemUtilUnsafe|objectFieldOffset|WARNING:\s*(please consider reporting|a restricted method|illegal reflective access)/i.test(l);
};

const isImportantLog = (raw: string) => {
  const l = stripAnsi(normalizeMinecraftLogLine(raw)).trim();
  if (isRoutineJvmWarning(raw) || /^\[System/i.test(l)) return false;
  if (/\b(ERROR|FATAL|SEVERE|EXCEPTION)\b|Can't keep up|crash|failed|failure|timed out|timeout|out of memory/i.test(l)) return true;
  if (/\btps\b|players online|There are \d+ of a max|joined the game|left the game|lost connection|logged in|logged out|server started|server stopped|saving worlds|saving players|stopping server|ready for connections|Done(?:\s*\([^)]*\))?!?\s*For help|Starting minecraft server version|Preparing level|Preparing spawn area|Loading properties|Default game type|Starting GS4 status listener|Starting remote control listener/i.test(l)) return true;
  if (/\bWARN(?:ING)?\b/i.test(l) && !isRoutineJvmWarning(raw)) return true;
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

function ConnPill({ state }: { state: "connecting" | "live" | "reconnecting" | "offline" }) {
  const live = state === "live";
  const connecting = state === "connecting" || state === "reconnecting";
  const label = live ? "Live" : connecting ? (state === "connecting" ? "Connecting" : "Reconnecting") : "Offline";
  const tone = live ? "text-emerald-400" : connecting ? "text-amber-300" : "text-red-400";
  return (
    <span className="snx-connection-badge flex items-center gap-2 px-3 py-1 rounded-sm">
      <span className="relative flex h-2 w-2">
        {(live || connecting) && (
          <span
            className={`absolute inset-0 rounded-full ${live ? "bg-emerald-400" : "bg-amber-300"}`}
            style={{ animation: "qx-ping 1.6s cubic-bezier(0,0,0.2,1) infinite" }}
          />
        )}
        <span
          className={`relative rounded-full h-2 w-2 transition-colors duration-500 ${
            live
              ? "bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.9)]"
              : connecting ? "bg-amber-300 shadow-[0_0_6px_rgba(252,211,77,0.8)]" : "bg-red-400 shadow-[0_0_6px_rgba(248,113,113,0.9)]"
          }`}
        />
      </span>
      <span
        className={`qx-display text-[9px] font-bold uppercase tracking-[0.18em] transition-colors duration-500 ${tone}`}
      >
        {label}
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
  const [connectionState, setConnectionState] = useState<"connecting" | "live" | "reconnecting" | "offline">("connecting");
  const [accessDenied, setAccessDenied] = useState("");
  const [ready, setReady] = useState(false);
  const [filter, setFilter] = useState<LogFilter>("all");
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
    setConnectionState("connecting");
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
      transports: ["polling", "websocket"],
      upgrade: true,
      reconnectionAttempts: Infinity,
      reconnectionDelay: 1000,
      reconnectionDelayMax: 5000,
      timeout: 10000,
    });
    sockRef.current = socket;

    socket.on("connect", () => {
      setAccessDenied("");
      setConnectionState("connecting");
      socket.emit("joinServer", serverId);
    });

    socket.on("server_joined", () => {
      setConnectionState("live");
    });

    socket.on("server_access_denied", (payload: { error?: string }) => {
      const message = payload?.error || "You are not authorized to view this server console.";
      setConnectionState("offline");
      setAccessDenied(message);
      // Keep transport/auth diagnostics out of the Minecraft log stream.
    });

    socket.on("log", (data: string) => {
      if (typeof data !== "string") return;
      const lines = data.split(/\n/).map(normalizeMinecraftLogLine).filter(isMinecraftLogLine).slice(-MAX_LOG_LINES).map(line => line.slice(0, 10000));
      pendingLogs.current = [...pendingLogs.current, ...lines].slice(-MAX_LOG_LINES);
    });

    socket.on("disconnect", () => {
      setConnectionState("reconnecting");
      // Connection state is shown by the live indicator; do not pollute Minecraft logs.
    });

    socket.on("clear_logs", () => {
      pendingLogs.current = [];
      setLogs([]);
    });

    socket.on("connect_error", (e: Error & { description?: unknown; context?: unknown; data?: { message?: string } }) => {
      setConnectionState("reconnecting");
      const detail = [
        e.message,
        e.data?.message,
        typeof e.description === "string" ? e.description : "",
        typeof e.context === "string" ? e.context : "",
      ].filter(Boolean).join(" — ");
      setAccessDenied(`Console connection failed: ${detail || "unknown connection error"}`);
    });

    const onReconnectFailed = () => {
      setConnectionState("offline");
      setAccessDenied("Console unavailable; retrying when the panel connection returns.");
    };
    socket.io.on("reconnect_failed", onReconnectFailed);

    return () => {
      window.clearInterval(flush);
      pendingLogs.current = [];
      socket.emit("leaveServer", serverId);
      socket.io.off("reconnect_failed", onReconnectFailed);
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
      // Show the command and its RCON response in the console, like a classic server terminal.
      setLogs(previous => [...previous, `> ${cmd}`].slice(-MAX_LOG_LINES));
      try {
        const response = await axios.post(`/api/servers/${serverId}/command`, { command: cmd }, { timeout: 30000 });
        const output = String(response.data?.output || "").trim();
        setLogs(previous => [...previous, ...(output ? output.split(/\\r?\\n/) : ["Command sent; no response text returned."])].slice(-MAX_LOG_LINES));
      } catch (err: any) {
        const message = String(err.response?.data?.error || err.message || "Command failed");
        setLogs(previous => [...previous, `[ERROR] ${message}`].slice(-MAX_LOG_LINES));
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

  /* ── Plain Minecraft log renderer: no synthetic LOG/INFO badges or wrappers ── */
  const renderLine = useCallback((raw: string): React.ReactNode => {
    // Keep the original prefix for severity detection, but hide Minecraft's
    // timestamp/level prefix in the visual console for a cleaner Pterodactyl-style view.
    const log = normalizeMinecraftLogLine(raw);
    const level = levelOf(log);
    const displayLog = log.replace(/^\\[\\d{2}:\\d{2}:\\d{2}(?:\\.\\d{1,3})?(?:\\s+(?:TRACE|DEBUG|INFO|WARN(?:ING)?|ERROR|FATAL|SEVERE))?\\]\\s*:?\\s*/i, "").trimStart();
    const color = level === "error" ? "text-red-300" : level === "warn" ? "text-amber-200" : "text-zinc-300";
    const lineSize = terminalFontSize === "small" ? "text-[10px]" : terminalFontSize === "large" ? "text-sm" : "text-[11px] sm:text-xs";
    return (
      <span className={`flex-1 min-w-0 ${wrapLines ? "break-words whitespace-pre-wrap" : "whitespace-pre"} ${lineSize} leading-[1.6] ${color}`}>
        {displayLog || log}
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
                      Minecraft Console
                    </h1>
                    <p className="qx-mono text-[8px] sm:text-[9px] text-slate-500 truncate">
                      stream :: {serverId}
                    </p>
                  </div>
                </div>

                <div className="flex items-center gap-1 sm:gap-2 shrink-0">
                  <span className="hidden lg:block"><Clock /></span>
                  <ConnPill state={connectionState} />
                  {accessDenied && <span role="status" className="max-w-[220px] truncate text-[9px] font-medium text-amber-200" title={accessDenied}>{accessDenied}</span>}
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

                {!atBottom && logs.length > 0 && (
                  <div className="sticky bottom-2 z-20 flex justify-end pointer-events-none">
                    <button
                      type="button"
                      onClick={jumpToBottom}
                      className="qx-display text-[9px] font-bold uppercase tracking-[0.14em] px-2.5 py-1.5 bg-zinc-950/95 text-zinc-200 border border-white/15 rounded-md hover:bg-white/[0.06] transition-colors pointer-events-auto shadow-lg"
                    >
                      <ChevronDown size={11} className="inline mr-1" />
                      Tail
                    </button>
                  </div>
                )}
              </div>

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
