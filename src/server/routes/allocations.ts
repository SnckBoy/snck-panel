import express, { Request, Response } from "express";
import crypto from "crypto";
import net from "net";
import { readJSON, writeJSON } from "../services/db.js";
import { requireAdmin, requireAuth } from "../middleware/auth.js";
import { audit, rateLimit } from "../services/security.js";

const router = express.Router();
// Authenticated users may see only free, unassigned allocations on public nodes.
router.get("/available", requireAuth, async (req: Request, res: Response) => {
  const user = (req as any).user;
  const settings = await readJSON("settings.json") || {};
  if (user.role !== "admin" && user.role !== "owner" && !settings.freeService?.enabled) return res.status(403).json({ error: "Free server creation is disabled." });
  const nodes = await readJSON("nodes.json") || [];
  const visibleIds = new Set(nodes.filter((n:any) => n.visibility !== "private" && !n.disabled && !n.maintenance && (user.role === "admin" || user.role === "owner" || !n.freeServiceLocked)).map((n:any) => String(n.id)));
  const allocations = await readJSON(file) || [];
  res.json(allocations.filter((a:any) => visibleIds.has(String(a.nodeId)) && !a.assignedServerId));
});
router.use(requireAdmin, rateLimit());
const file = "allocations.json";

const findNode = async (nodeId: string) => {
  const nodes = await readJSON("nodes.json") || [];
  return nodes.find((node: any) => node.id === nodeId);
};

const parsePort = (value: unknown) => {
  const port = Number(value);
  return Number.isInteger(port) && port >= 1 && port <= 65535 ? port : null;
};

const validateAddress = (value: unknown, ipVersion: string) => {
  const ip = String(value || "").trim();
  const family = net.isIP(ip);
  if (!family || `IPv${family}` !== ipVersion) return null;
  return ip;
};

router.get("/", async (_req: Request, res: Response) => {
  res.json(await readJSON(file) || []);
});

router.post("/", async (req: Request, res: Response) => {
  const nodeId = String(req.body?.nodeId || "").trim();
  const ipVersion = String(req.body?.ipVersion || "IPv4");
  const ip = validateAddress(req.body?.ip, ipVersion);
  const start = parsePort(req.body?.portStart);
  const end = parsePort(req.body?.portEnd ?? req.body?.portStart);
  if (!nodeId || !ip || !start || !end) {
    return res.status(400).json({ error: "nodeId, a valid IP, and ports from 1 to 65535 are required" });
  }
  if (start > end) return res.status(400).json({ error: "Start port must be less than or equal to end port" });
  if (!(await findNode(nodeId))) return res.status(400).json({ error: "Node not found" });

  const allocations = await readJSON(file) || [];
  const overlaps = allocations.some((allocation: any) =>
    String(allocation.nodeId) === nodeId && allocation.ip === ip && Math.max(start, Number(allocation.portStart)) <= Math.min(end, Number(allocation.portEnd))
  );
  if (overlaps) return res.status(409).json({ error: "IP/port range overlaps an existing allocation" });

  const isPrimary = Boolean(req.body?.primary);
  if (isPrimary) for (const allocation of allocations) if (allocation.nodeId === String(nodeId)) allocation.primary = false;
  const createdAt = new Date().toISOString();
  const created = [];
  for (let port = start; port <= end; port += 1) {
    created.push({
      id: crypto.randomUUID(),
      nodeId: String(nodeId),
      ip,
      ipVersion,
      portStart: port,
      portEnd: port,
      assignedServerId: null,
      // A range creates independent ports; only the first can be primary.
      primary: isPrimary && port === start,
      createdAt,
    });
  }
  allocations.push(...created);
  await writeJSON(file, allocations);
  await audit("allocation.created", req, {
    allocationIds: created.map((item: any) => item.id),
    nodeId: String(nodeId),
    ip,
    portStart: start,
    portEnd: end,
    count: created.length,
  });
  return res.status(201).json({ allocations: created, count: created.length });
});

router.post("/:id/primary", async (req: Request, res: Response) => {
  const allocations = await readJSON(file) || [];
  const allocation = allocations.find((value: any) => value.id === req.params.id);
  if (!allocation) return res.status(404).json({ error: "Allocation not found" });
  for (const value of allocations) if (value.nodeId === allocation.nodeId) value.primary = value.id === allocation.id;
  await writeJSON(file, allocations);
  await audit("allocation.primary.changed", req, { allocationId: allocation.id, nodeId: allocation.nodeId });
  return res.json(allocation);
});

router.delete("/:id", async (req: Request, res: Response) => {
  const allocations = await readJSON(file) || [];
  const allocation = allocations.find((value: any) => value.id === req.params.id);
  if (!allocation) return res.status(404).json({ error: "Allocation not found" });
  if (allocation.assignedServerId) return res.status(409).json({ error: "Unassign the server first" });
  await writeJSON(file, allocations.filter((value: any) => value.id !== allocation.id));
  await audit("allocation.deleted", req, { allocationId: allocation.id, nodeId: allocation.nodeId });
  return res.json({ success: true });
});

router.post("/:id/assign", async (req: Request, res: Response) => {
  const allocations = await readJSON(file) || [];
  const allocation = allocations.find((value: any) => value.id === req.params.id);
  if (!allocation) return res.status(404).json({ error: "Allocation not found" });
  if (allocation.assignedServerId) return res.status(409).json({ error: "Allocation already assigned" });

  const serverId = String(req.body?.serverId || "");
  const servers = await readJSON("servers.json") || [];
  const server = servers.find((value: any) => value.id === serverId);
  if (!server) return res.status(404).json({ error: "Server not found" });
  if (String(server.nodeId || "local") !== String(allocation.nodeId)) return res.status(409).json({ error: "Server and allocation belong to different nodes" });
  const serverPort = Number(server.port);
  if (!Number.isInteger(serverPort) || serverPort < allocation.portStart || serverPort > allocation.portEnd) {
    return res.status(400).json({ error: "Server port is outside the allocation range" });
  }
  if (allocations.some((value: any) => value.assignedServerId === serverId)) {
    return res.status(409).json({ error: "Server already has an allocation" });
  }

  allocation.assignedServerId = serverId;
  await writeJSON(file, allocations);
  await audit("allocation.assigned", req, { allocationId: allocation.id, nodeId: allocation.nodeId, serverId });
  return res.json(allocation);
});

router.post("/:id/unassign", async (req: Request, res: Response) => {
  const allocations = await readJSON(file) || [];
  const allocation = allocations.find((value: any) => value.id === req.params.id);
  if (!allocation) return res.status(404).json({ error: "Allocation not found" });
  const previousServerId = allocation.assignedServerId;
  allocation.assignedServerId = null;
  await writeJSON(file, allocations);
  await audit("allocation.unassigned", req, { allocationId: allocation.id, nodeId: allocation.nodeId, serverId: previousServerId });
  return res.json(allocation);
});

export default router;
