import assert from "node:assert/strict";
import fs from "node:fs/promises";
import os from "node:os";
import path from "node:path";
import { fileURLToPath } from "node:url";

const originalCwd = process.cwd();
const tempDir = await fs.mkdtemp(path.join(os.tmpdir(), "snck-node-lifecycle-test-"));
const jwtSecret = "node-lifecycle-test-jwt-secret-long-enough";
process.env.JWT_SECRET = jwtSecret;
process.env.NODE_ENCRYPTION_KEY = "node-lifecycle-test-encryption-key";
process.env.NODE_HEARTBEAT_TIMEOUT_MS = "45000";
process.chdir(tempDir);
let server: any;
try {
  await fs.mkdir(path.join(tempDir, ".data"), { recursive: true });
  const legacyNodes = Array.from({ length: 4 }, (_, index) => ({
    id: `legacy-node-${index + 1}`, name: `Existing ${index + 1}`, hostname: `old-${index + 1}.example.test`,
    fqdn: `old-${index + 1}.example.test`, apiPort: 6768, sftpPort: 2022, tls: false,
    memory: 8192, disk: 100, cpu: 400, disabled: false, createdAt: new Date().toISOString(),
  }));
  await fs.writeFile(path.join(tempDir, ".data", "nodes.json"), JSON.stringify(legacyNodes));
  await fs.writeFile(path.join(tempDir, ".data", "users.json"), JSON.stringify([{ id: "node-test-admin", username: "node-test-admin", role: "admin", passwordVersion: 0 }]));

  const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
  const expressModule = await import("express");
  const jwtModule = await import("jsonwebtoken");
  const nodeRoutes = (await import(path.join(root, "src/server/routes/nodes.ts"))).default;
  const agentRoutes = (await import(path.join(root, "src/server/routes/nodeAgent.ts"))).default;
  const app = expressModule.default();
  app.use(expressModule.default.json());
  app.use("/api/nodes", nodeRoutes);
  app.use("/api/node-agent", agentRoutes);
  server = app.listen(0, "127.0.0.1");
  await new Promise<void>((resolve, reject) => { server.once("listening", resolve); server.once("error", reject); });
  const address = server.address();
  const base = `http://127.0.0.1:${address.port}`;
  const adminToken = jwtModule.default.sign({ id: "node-test-admin", role: "admin", passwordVersion: 0 }, jwtSecret, { expiresIn: "5m" });
  const adminHeaders = { Authorization: `Bearer ${adminToken}`, "Content-Type": "application/json" };
  const createPayload = (index: number) => ({
    name: `Additional node ${index}`, hostname: `node-${index}.example.test`, fqdn: `node-${index}.example.test`,
    publicIp: `198.51.100.${index}`, apiPort: 6768, sftpPort: 2022, tls: false, behindProxy: false,
    memory: 8192, disk: 100, cpu: 400, memoryOverallocate: 0, diskOverallocate: 0,
    visibility: "public", createCloudflareDns: false,
  });

  const createdResponses = await Promise.all(Array.from({ length: 6 }, (_, index) => fetch(`${base}/api/nodes`, {
    method: "POST", headers: adminHeaders, body: JSON.stringify(createPayload(index + 1)),
  })));
  for (const response of createdResponses) if (response.status !== 201) assert.fail(`node creation failed (${response.status}): ${await response.text()}`);
  const created = await Promise.all(createdResponses.map((response) => response.json() as Promise<any>));
  assert.equal(new Set(created.map((node) => node.id)).size, 6, "each created node has its own UUID");
  assert.ok(created.every((node) => typeof node.setupToken === "string" && node.setupToken.length > 20), "each node has a distinct one-time setup token");

  const afterCreateResponse = await fetch(`${base}/api/nodes`, { headers: adminHeaders });
  assert.equal(afterCreateResponse.status, 200);
  const afterCreate = await afterCreateResponse.json() as any[];
  assert.equal(afterCreate.length, 10, "all four legacy records and six new records remain in inventory");
  assert.deepEqual(afterCreate.slice(0, 4).map((node) => node.id), legacyNodes.map((node) => node.id), "legacy node order and identity stay intact");
  const publicInventoryResponse = await fetch(`${base}/api/nodes/public`);
  const publicInventory = await publicInventoryResponse.json() as any[];
  assert.equal(publicInventory.length, 10, "public inventory is also not truncated");

  const registrations = await Promise.all(created.map((node) => fetch(`${base}/api/node-agent/register`, {
    method: "POST", headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ nodeId: node.id, setupToken: node.setupToken, daemonVersion: "test-daemon" }),
  })));
  for (const response of registrations) if (response.status !== 200) assert.fail(`registration failed (${response.status}): ${await response.text()}`);
  const registered = await Promise.all(registrations.map((response) => response.json() as Promise<any>));
  assert.equal(new Set(registered.map((node) => node.credential)).size, 6, "node credentials are unique");

  const heartbeats = await Promise.all(registered.map((node) => fetch(`${base}/api/node-agent/heartbeat`, {
    method: "POST", headers: { "Content-Type": "application/json", Authorization: `Bearer ${node.credential}` },
    body: JSON.stringify({ nodeId: node.nodeId, stats: { docker: true, daemonVersion: "test-daemon" } }),
  })));
  for (const response of heartbeats) if (response.status !== 200) assert.fail(`authenticated heartbeat failed (${response.status}): ${await response.text()}`);

  const inventoryResponse = await fetch(`${base}/api/nodes`, { headers: adminHeaders });
  const inventory = await inventoryResponse.json() as any[];
  assert.equal(inventory.length, 10);
  for (const node of registered) {
    const actual = inventory.find((item) => item.id === node.nodeId);
    assert.ok(actual, `node ${node.nodeId} remains present`);
    assert.equal(actual.status, "ONLINE", "status derives from received authenticated heartbeat");
    assert.equal(actual.lastStats?.docker, true, "Docker status comes from actual heartbeat telemetry");
  }

  const lastNode = registered[5];
  const editResponse = await fetch(`${base}/api/nodes/${lastNode.nodeId}`, {
    method: "PATCH", headers: adminHeaders, body: JSON.stringify({ name: "Node six edited" }),
  });
  assert.equal(editResponse.status, 200, "sixth node remains editable by its UUID");
  assert.equal((await editResponse.json() as any).name, "Node six edited");
  const disableResponse = await fetch(`${base}/api/nodes/${lastNode.nodeId}/disable`, { method: "POST", headers: adminHeaders });
  assert.equal(disableResponse.status, 200);
  const disabledHeartbeat = await fetch(`${base}/api/node-agent/heartbeat`, {
    method: "POST", headers: { "Content-Type": "application/json", Authorization: `Bearer ${lastNode.credential}` },
    body: JSON.stringify({ nodeId: lastNode.nodeId, stats: { docker: true } }),
  });
  assert.equal(disabledHeartbeat.status, 403, "disabled-node heartbeat remains rejected");
  const enableResponse = await fetch(`${base}/api/nodes/${lastNode.nodeId}/enable`, { method: "POST", headers: adminHeaders });
  assert.equal(enableResponse.status, 200);
  const reenabledHeartbeat = await fetch(`${base}/api/node-agent/heartbeat`, {
    method: "POST", headers: { "Content-Type": "application/json", Authorization: `Bearer ${lastNode.credential}` },
    body: JSON.stringify({ nodeId: lastNode.nodeId, stats: { docker: true, daemonVersion: "test-daemon" } }),
  });
  assert.equal(reenabledHeartbeat.status, 200);
  const wrongCredential = await fetch(`${base}/api/node-agent/heartbeat`, {
    method: "POST", headers: { "Content-Type": "application/json", Authorization: "Bearer wrong-node-credential" },
    body: JSON.stringify({ nodeId: lastNode.nodeId, stats: { docker: true } }),
  });
  assert.equal(wrongCredential.status, 401, "wrong credentials remain rejected");
  const replay = await fetch(`${base}/api/node-agent/register`, {
    method: "POST", headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ nodeId: lastNode.nodeId, setupToken: created[5].setupToken, daemonVersion: "test-daemon" }),
  });
  assert.equal(replay.status, 401, "one-time setup token replay remains rejected");
  const unauthenticatedInventory = await fetch(`${base}/api/nodes`);
  assert.equal(unauthenticatedInventory.status, 401, "node administration still requires valid admin authentication");

  const missingZone = await fetch(`${base}/api/nodes`, {
    method: "POST", headers: adminHeaders,
    body: JSON.stringify({ ...createPayload(7), createCloudflareDns: true, cloudflareZoneId: "" }),
  });
  assert.equal(missingZone.status, 400, "automatic DNS requires an explicitly selected zone");
  const missingPublicIp = await fetch(`${base}/api/nodes`, {
    method: "POST", headers: adminHeaders,
    body: JSON.stringify({ ...createPayload(8), createCloudflareDns: true, cloudflareZoneId: "a".repeat(32), publicIp: "" }),
  });
  assert.equal(missingPublicIp.status, 400, "automatic A record requires a real public IPv4 address");

  const diskNodes = JSON.parse(await fs.readFile(path.join(tempDir, ".data", "nodes.json"), "utf8"));
  assert.equal(diskNodes.length, 10, "atomic creation keeps all concurrent node records");
  assert.equal(new Set(diskNodes.map((node: any) => node.id)).size, 10);
  const setupTokens = JSON.parse(await fs.readFile(path.join(tempDir, ".data", "node_setup_tokens.json"), "utf8"));
  assert.equal(setupTokens.length, 6, "concurrent token issuance preserves each one-time token");
  assert.equal(setupTokens.filter((token: any) => token.used).length, 6, "each registration consumes exactly its own setup token");

  console.log("Node lifecycle integration tests passed: legacy nodes preserved, six concurrent creates, six one-time registrations, six authenticated heartbeats, inventory/status/Docker reporting, DNS preflight, database-backed admin auth, and credential rejection.");
} finally {
  if (server) await new Promise<void>((resolve) => server.close(() => resolve()));
  process.chdir(originalCwd);
  await fs.rm(tempDir, { recursive: true, force: true });
}
