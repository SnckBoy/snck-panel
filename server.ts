import "dotenv/config";
import express from "express";
import path from "path";
import cors from "cors";
import { createServer as createHttpServer } from "http";
import { createServer as createHttpsServer } from "https";
import { Server as SocketIOServer } from "socket.io";
import { createServer as createViteServer } from "vite";
import fs from "fs-extra";
import jwt from "jsonwebtoken";
import archiver from "archiver";

if (!process.env.JWT_SECRET || process.env.JWT_SECRET.length < 16) {
  console.error(
    "\n[FATAL] JWT_SECRET is missing or too short in your .env file.\n" +
    "Generate one and add it, e.g.:\n" +
    "  echo \"JWT_SECRET=$(openssl rand -hex 32)\" >> .env\n" +
    "(install.sh does this automatically for you.)\n"
  );
  process.exit(1);
}

const app = express();

// ---------------------------------------------------------------------------
// CORS / origin hardening.
// Requests without an Origin header (same-origin requests, curl, health
// checks, node-daemon heartbeats) are always allowed. In production, only the
// panel's own public origin plus PANEL_ALLOWED_ORIGINS entries are accepted;
// in development the common local dev origins remain permitted.
// ---------------------------------------------------------------------------
const extraOrigins = String(process.env.PANEL_ALLOWED_ORIGINS || "")
  .split(",")
  .map((entry) => entry.trim().replace(/\/+$/, ""))
  .filter(Boolean);

function getAllowedOrigins(): string[] {
  const origins = new Set<string>();
  if (process.env.NODE_ENV === "production") {
    const publicOrigin = String(process.env.PANEL_PUBLIC_URL || process.env.PANEL_URL || "").trim().replace(/\/+$/, "");
    if (publicOrigin) origins.add(publicOrigin);
    origins.add("http://localhost");
    origins.add("http://127.0.0.1");
    for (const extra of extraOrigins) origins.add(extra);
  } else {
    origins.add("http://localhost:5173");
    origins.add("http://127.0.0.1:5173");
    origins.add("http://localhost:3000");
    origins.add("http://127.0.0.1:3000");
    for (const extra of extraOrigins) origins.add(extra);
  }
  return Array.from(origins);
}

function originAllowed(origin: string | undefined): boolean {
  if (!origin) return true;
  return getAllowedOrigins().includes(String(origin).trim().replace(/\/+$/, ""));
}

const corsOptions = {
  origin(origin: string | undefined, callback: (err: Error | null, ok?: boolean) => void) {
    callback(null, originAllowed(origin));
  },
  credentials: true,
  methods: ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
  allowedHeaders: ["Content-Type", "Authorization"],
  exposedHeaders: ["Content-Disposition"],
};

const httpServer = process.env.PANEL_TLS_KEY && process.env.PANEL_TLS_CERT ? createHttpsServer({key:fs.readFileSync(process.env.PANEL_TLS_KEY),cert:fs.readFileSync(process.env.PANEL_TLS_CERT)},app) : createHttpServer(app);
export const io = new SocketIOServer(httpServer, {
  cors: {
    origin(origin: string | undefined, callback: (err: Error | null, ok?: boolean) => void) {
      if (originAllowed(origin)) return callback(null, true);
      callback(new Error("Origin not allowed by the panel CORS policy"));
    },
    credentials: true,
    methods: ["GET", "POST"],
  },
});
app.set("io", io);

// Initialize data folders
const DATA_DIR = path.join(process.cwd(), ".data");
const SERVERS_DIR = path.join(DATA_DIR, "servers");
const BACKUPS_DIR = path.join(process.cwd(), "backups");

fs.ensureDirSync(DATA_DIR);
fs.ensureDirSync(SERVERS_DIR);
fs.ensureDirSync(BACKUPS_DIR);
fs.ensureDirSync(path.join(DATA_DIR, "temp"));

if (!fs.existsSync(path.join(DATA_DIR, "users.json"))) fs.writeFileSync(path.join(DATA_DIR, "users.json"), "[]");
if (!fs.existsSync(path.join(DATA_DIR, "servers.json"))) fs.writeFileSync(path.join(DATA_DIR, "servers.json"), "[]");
if (!fs.existsSync(path.join(DATA_DIR, "settings.json"))) fs.writeFileSync(path.join(DATA_DIR, "settings.json"), "{}");

import { attachContainerSocket, getContainerLogs, setSocketIO } from "./src/server/services/docker.js";
setSocketIO(io);
import { getJwtSecret } from "./src/server/services/security.js";

io.use((socket, next) => {
  const token = socket.handshake.auth.token;
  if (!token) return next(new Error("Authentication error"));
  try {
    const verified = jwt.verify(token, getJwtSecret());
    (socket as any).user = verified;
    next();
  } catch (err) {
    next(new Error("Authentication error"));
  }
});

io.on("connection", (socket) => {
  type Subscription = { cancelled: boolean; timer?: ReturnType<typeof setInterval>; polling: boolean; previous: string[] };
  const subscriptions = new Map<string, Subscription>();
  const leave = (id: string) => {
    const subscription = subscriptions.get(id);
    if (subscription) { subscription.cancelled = true; clearInterval(subscription.timer); }
    subscriptions.delete(id);
    socket.leave(`server_${id}`);
  };
  socket.on("joinServer", async (serverId) => {
    const requestedId = String(serverId || "");
    if (!/^[A-Za-z0-9_-]{1,160}$/.test(requestedId)) {
      socket.emit("server_access_denied", { serverId: requestedId, error: "Invalid server ID" });
      return;
    }
    leave(requestedId);
    const subscription: Subscription = { cancelled: false, polling: false, previous: [] };
    subscriptions.set(requestedId, subscription);
    const active = () => socket.connected && !subscription.cancelled;
    // Daemons return a rolling log tail. Emit only the suffix not already delivered.
    const emitSnapshot = (text: string) => {
      const lines = text.split(/\r?\n/).filter(line => line.trim());
      let overlap = Math.min(subscription.previous.length, lines.length);
      while (overlap > 0 && !subscription.previous.slice(-overlap).every((line, index) => line === lines[index])) overlap--;
      const fresh = lines.slice(overlap);
      subscription.previous = lines.slice(-200);
      if (fresh.length) socket.emit("log", fresh.join("\n") + "\n");
    };
    try {
      const servers = JSON.parse(await fs.readFile(path.join(DATA_DIR, "servers.json"), "utf8"));
      if (!active()) return;
      const server = Array.isArray(servers) ? servers.find((s: any) => s.id === requestedId) : null;
      const user = (socket as any).user || {};
      const staff = user.role === "admin" || user.role === "owner";
      const subUser = Array.isArray(server?.subUsers) && server.subUsers.some((entry: any) => String(entry?.userId) === String(user.id));
      if (!server || (!staff && server.owner !== user.id && !subUser)) {
        leave(requestedId);
        socket.emit("server_access_denied", { serverId: requestedId, error: "You are not authorized to access this server" });
        return;
      }
      socket.join(`server_${requestedId}`);
      socket.emit("server_joined", { serverId: requestedId });
      if (!server.containerId) return;
      const logs = await getContainerLogs(server.containerId, server.nodeId);
      if (!active()) return;
      if (logs) emitSnapshot(logs);
      await attachContainerSocket(server.containerId, requestedId, server.nodeId);
      if (!active()) return;
      if (server.nodeId && server.nodeId !== "local") {
        subscription.timer = setInterval(async () => {
          if (!active() || subscription.polling) return;
          subscription.polling = true;
          try {
            const latest = await getContainerLogs(server.containerId, server.nodeId);
            if (active() && latest) emitSnapshot(latest);
          } catch { /* Retain history while the daemon reconnects. */ }
          finally { subscription.polling = false; }
        }, 3000);
      }
    } catch (error) {
      if (!active()) return;
      leave(requestedId);
      console.error("Socket server join failed", error);
      socket.emit("server_access_denied", { serverId: requestedId, error: "Unable to load server console data" });
    }
  });
  socket.on("leaveServer", (serverId) => leave(String(serverId || "")));
  socket.on("disconnect", () => { for (const id of subscriptions.keys()) leave(id); });
});

const PORT = Number(process.env.PORT || 6767);
const HOST = process.env.HOST || "0.0.0.0";

// Actual file uploads go through multer (disk-backed, see servers.ts), which never
// touches these parsers. A 50gb limit here only meant any client could send an
// oversized JSON/form body and exhaust server RAM before a single upload happened.
app.use(express.json({ limit: "10mb" }));
app.use(express.urlencoded({ extended: true, limit: "10mb" }));
app.use(cors(corsOptions));
app.get("/health", (_req, res) => res.json({ ok: true, service: "snck-panel", timestamp: new Date().toISOString() }));

import apiRoutes from "./src/server/routes/api.js";
app.use("/api", apiRoutes);

// Public bootstrap artifacts used by the one-time node setup command. The setup
// token itself is still validated by /api/node-agent/register and expires quickly.
app.get("/node.sh", (_req, res) => {
  const installer = path.join(process.cwd(), "node-daemon", "install.sh");
  if (!fs.existsSync(installer)) return res.status(404).send("Node installer unavailable");
  res.type("text/x-shellscript").send(fs.readFileSync(installer, "utf8"));
});

app.get("/shironex-node.tar.gz", (_req, res) => {
  const daemonDir = path.join(process.cwd(), "node-daemon");
  if (!fs.existsSync(path.join(daemonDir, "package.json"))) return res.status(404).send("Node daemon unavailable");
  res.type("application/gzip");
  const archive = archiver("tar", { gzip: true });
  archive.on("error", (err: Error) => {
    console.error("Node bundle archive error:", err);
    if (!res.headersSent) res.status(500);
    res.end();
  });
  archive.pipe(res);
  for (const file of ["package.json", "tsconfig.json", "update.sh", "uninstall.sh"]) {
    const fullPath = path.join(daemonDir, file);
    if (fs.existsSync(fullPath)) archive.file(fullPath, { name: file });
  }
  archive.directory(path.join(daemonDir, "src"), "src");
  archive.finalize();
});

import { initSFTPServer } from "./src/server/services/sftp.js";

async function startServer() {
  await initSFTPServer();

  if (process.env.NODE_ENV !== "production") {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: "spa",
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), "dist");
    app.use(express.static(distPath));
    app.get("*", (req, res) => {
      res.sendFile(path.join(distPath, "index.html"));
    });
  }

  httpServer.listen(PORT, HOST, () => {
    console.log(`SNCK PANEL running on http://${HOST}:${PORT}`);
  });
}

startServer();

process.on('uncaughtException', (err) => {
  console.error('UNCAUGHT EXCEPTION:', err);
  fs.writeFileSync('crash.log', String(err.stack));
});
process.on('unhandledRejection', (reason, promise) => {
  console.error('UNHANDLED REJECTION:', reason);
  fs.writeFileSync('crash.log', String(reason));
});
