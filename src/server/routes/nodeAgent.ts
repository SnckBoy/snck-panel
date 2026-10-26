import express from "express";
import { readJSON, writeJSON, updateJSON } from "../services/db.js";
import { hashSecret, encryptSecret, rateLimit } from "../services/security.js";
import crypto from "crypto";

const r = express.Router();
r.use(express.json(), rateLimit());
const nodeFile = "nodes.json";
const tokenFile = "node_setup_tokens.json";
const isLoopback = (address: string) => ["127.0.0.1", "::1"].includes(address.replace(/^::ffff:/, ""));
const matchesBootstrapSecret = (provided: string, expected: string) => {
  const a = Buffer.from(provided), b = Buffer.from(expected);
  return a.length === b.length && a.length > 0 && crypto.timingSafeEqual(a, b);
};

r.post("/local-bootstrap", async (req, res) => {
  const remote = String(req.socket.remoteAddress || "");
  if (!isLoopback(remote)) return res.status(403).json({ error: "Local bootstrap is only available from the panel host" });
  if (!matchesBootstrapSecret(String(req.headers["x-shironex-bootstrap"] || ""), String(process.env.NODE_AUTH_SECRET || ""))) return res.status(401).json({ error: "Invalid local bootstrap secret" });
  const credential = crypto.randomBytes(48).toString("base64url");
  let port = Number(req.body?.port) || 6768;
  let created = false;
  await updateJSON<any[]>(nodeFile, (nodes) => {
    let node = nodes.find((item: any) => item.id === "local");
    if (!node) {
      created = true;
      node = { id: "local", name: "Local Node", description: "Panel host", hostname: "127.0.0.1", fqdn: "127.0.0.1", publicIp: "127.0.0.1", apiPort: port, tls: false, isLocal: true, disabled: false, createdAt: new Date().toISOString() };
      nodes.push(node);
    }
    port = Number(req.body?.port) || node.apiPort || 6768;
    node.apiPort = port; node.isLocal = true; node.hostname = "127.0.0.1"; node.fqdn = "127.0.0.1"; node.tls = false;
    node.disabled = false; node.maintenance = false; node.error = null; node.credential = encryptSecret(credential);
    node.credentialHash = hashSecret(credential); node.daemonVersion = String(req.body?.daemonVersion || node.daemonVersion || "1.1.0");
    node.lastHeartbeat = null; node.lastStats = null;
    return nodes;
  }, []);
  res.json({ nodeId: "local", credential, port, created });
});

r.post("/register", async (req, res) => {
  const { setupToken, nodeId, daemonVersion } = req.body || {};
  if (typeof nodeId !== "string" || typeof setupToken !== "string" || !setupToken) return res.status(401).json({ error: "Invalid or expired setup token" });
  const credential = crypto.randomBytes(48).toString("base64url");
  let tokenReserved = false;
  await updateJSON<any[]>(tokenFile, (tokens) => {
    const token = tokens.find((item: any) => item.nodeId === nodeId && item.hash === hashSecret(setupToken) && !item.used && item.expiresAt > Date.now());
    if (token) { token.used = true; tokenReserved = true; }
    return tokens;
  }, []);
  if (!tokenReserved) return res.status(401).json({ error: "Invalid or expired setup token" });
  let nodeFound = false;
  let daemonPort: number | null = null;
  await updateJSON<any[]>(nodeFile, (nodes) => {
    const node = nodes.find((item: any) => item.id === nodeId);
    if (!node) return nodes;
    nodeFound = true;
    node.credential = encryptSecret(credential); node.credentialHash = hashSecret(credential);
    node.daemonVersion = daemonVersion; node.lastHeartbeat = null;
    daemonPort = node.apiPort;
    return nodes;
  }, []);
  if (!nodeFound) return res.status(404).json({ error: "Node not found" });
  return res.json({ nodeId, credential, port: daemonPort });
});

r.post("/heartbeat", async (req, res) => {
  const bodyCredential = String(req.body?.credential || "");
  const header = String(req.headers.authorization || "");
  const credential = header.startsWith("Bearer ") ? header.slice(7) : bodyCredential;
  const { nodeId, stats } = req.body || {};
  if (typeof nodeId !== "string" || !credential) return res.status(401).json({ error: "Invalid node credentials" });
  let authFailed = false;
  let disabled = false;
  await updateJSON<any[]>(nodeFile, (nodes) => {
    const node = nodes.find((item: any) => item.id === nodeId);
    if (!node || hashSecret(credential) !== node.credentialHash) { authFailed = true; return nodes; }
    if (node.disabled) { disabled = true; return nodes; }
    node.lastHeartbeat = new Date().toISOString(); node.lastStats = stats; node.restartingAt = null;
    node.daemonVersion = stats?.daemonVersion || node.daemonVersion;
    return nodes;
  }, []);
  if (authFailed) return res.status(401).json({ error: "Invalid node credentials" });
  if (disabled) return res.status(403).json({ error: "Node disabled" });
  return res.json({ ok: true });
});

export default r;
