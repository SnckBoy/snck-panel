import fs from "node:fs/promises";
import path from "node:path";
import crypto from "node:crypto";
import { Pool } from "pg";

const databaseUrl = process.env.DATABASE_URL;
if (!databaseUrl) throw new Error("DATABASE_URL is required");
const dataDir = path.resolve(process.env.SNCK_DATA_DIR || ".data");
const backupDir = path.resolve(process.env.SNCK_MIGRATION_BACKUP_DIR || ".migration-backups");
const pool = new Pool({ connectionString: databaseUrl });
const id = (value: any) => {
  if (!value || typeof value !== "object" || Array.isArray(value)) {
    throw new Error("Migration data contains a non-object record");
  }
  // Assign once so fallback IDs remain consistent across all foreign-key fields.
  if (typeof value.id !== "string" || value.id.length === 0) value.id = crypto.randomUUID();
  return value.id;
};
const read = async (name: string): Promise<any[]> => {
  const filePath = path.join(dataDir, name);
  let raw: string;
  try {
    raw = await fs.readFile(filePath, "utf8");
  } catch (error: any) {
    if (error?.code === "ENOENT") return [];
    throw new Error(`Unable to read ${filePath}: ${error instanceof Error ? error.message : String(error)}`);
  }
  let parsed: unknown;
  try {
    parsed = JSON.parse(raw);
  } catch {
    throw new Error(`Refusing migration: ${filePath} contains invalid JSON. Restore or repair this file before retrying.`);
  }
  if (!Array.isArray(parsed)) {
    throw new Error(`Refusing migration: ${filePath} must contain a JSON array.`);
  }
  for (const record of parsed) {
    if (!record || typeof record !== "object" || Array.isArray(record)) {
      throw new Error(`Refusing migration: ${filePath} contains a record that is not a JSON object.`);
    }
  }
  return parsed;
};
const safeBackup = async () => {
  await fs.mkdir(backupDir, { recursive: true });
  const target = path.join(backupDir, new Date().toISOString().replace(/[:.]/g, "-") + "-json-backup");
  await fs.cp(dataDir, target, { recursive: true, errorOnExist: true });
  return target;
};

async function main() {
  const backup = await safeBackup();
  const client = await pool.connect();
  const report: Record<string, number | string> = { backup };
  try {
    await client.query("BEGIN");
    const users = await read("users.json");
    const nodes = await read("nodes.json");
    const servers = await read("servers.json");
    const allocations = await read("allocations.json");
    for (const u of users) await client.query(`INSERT INTO users(id,username,email,password_hash,role,disabled,created_at) VALUES($1,$2,$3,$4,$5,$6,$7) ON CONFLICT(id) DO NOTHING`, [id(u), String(u.username || "user-" + id(u).slice(0,8)), String(u.email || `${id(u)}@invalid.local`), String(u.passwordHash || u.password_hash || "!MIGRATION_REQUIRES_PASSWORD_RESET"), String(u.role || "user"), Boolean(u.disabled), u.createdAt || new Date().toISOString()]);
    for (const n of nodes) await client.query(`INSERT INTO nodes(id,name,address,status,maintenance,daemon_version,created_at) VALUES($1,$2,$3,$4,$5,$6,$7) ON CONFLICT(id) DO NOTHING`, [id(n), String(n.name || "Node"), String(n.hostname || n.fqdn || n.publicIp || "127.0.0.1"), String(n.status || "offline").toLowerCase(), Boolean(n.maintenance), n.daemonVersion || null, n.createdAt || new Date().toISOString()]);
    for (const s of servers) {
      if (!s.ownerId || !s.nodeId) continue;
      await client.query(`INSERT INTO servers(id,owner_id,node_id,name,description,status,runtime,minecraft_version,server_type,memory_mb,cpu_percent,disk_mb,root_path,suspended,created_at) VALUES($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15) ON CONFLICT(id) DO NOTHING`, [id(s), String(s.ownerId), String(s.nodeId), String(s.name || "Server"), String(s.description || ""), String(s.status || "stopped"), String(s.runtime || "docker"), s.minecraftVersion || null, s.serverType || null, Number(s.memory || 1024), Number(s.cpu || 100), Number(s.disk || 10240), String(s.serverDirectory || s.rootPath || `/var/lib/snck/servers/${id(s)}`), Boolean(s.suspended), s.createdAt || new Date().toISOString()]);
    }
    for (const a of allocations) {
      if (!a.nodeId || !a.ip || !a.port) continue;
      await client.query(`INSERT INTO allocations(id,node_id,server_id,ip,port) VALUES($1,$2,$3,$4,$5) ON CONFLICT(node_id,ip,port) DO UPDATE SET server_id=EXCLUDED.server_id`, [id(a), String(a.nodeId), a.serverId ? String(a.serverId) : null, String(a.ip), Number(a.port)]);
    }
    await client.query("COMMIT");
    report.users = users.length; report.nodes = nodes.length; report.servers = servers.length; report.allocations = allocations.length;
    console.log(JSON.stringify({ success: true, report }, null, 2));
  } catch (error) {
    await client.query("ROLLBACK");
    console.error(JSON.stringify({ success: false, backup, error: error instanceof Error ? error.message : String(error) }, null, 2));
    process.exitCode = 1;
  } finally { client.release(); await pool.end(); }
}
main().catch((error) => { console.error(error); process.exitCode = 1; });
