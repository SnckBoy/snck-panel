import { randomUUID } from "node:crypto";
import { Pool, PoolClient } from "pg";

const url = process.env.DATABASE_URL;
export const relationalEnabled = Boolean(url);
export const pool = relationalEnabled ? new Pool({ connectionString: url, max: Number(process.env.DB_POOL_SIZE || 10), idleTimeoutMillis: 30_000, connectionTimeoutMillis: 10_000 }) : null;

export async function transaction<T>(work: (client: PoolClient) => Promise<T>): Promise<T> {
  if (!pool) throw new Error("DATABASE_URL is not configured");
  const client = await pool.connect();
  try {
    await client.query("BEGIN");
    const result = await work(client);
    await client.query("COMMIT");
    return result;
  } catch (error) {
    await client.query("ROLLBACK");
    throw error;
  } finally { client.release(); }
}

export async function initializeRelationalDatabase(schemaSql: string) {
  if (!pool) return false;
  await pool.query(schemaSql);
  return true;
}

export async function createAuditLog(input: { actorId?: string; action: string; targetType: string; targetId?: string; result: string; requestId?: string; ip?: string; metadata?: Record<string, unknown> }) {
  if (!pool) return;
  await pool.query(
    `INSERT INTO audit_logs (id, actor_id, action, target_type, target_id, result, request_id, ip, metadata)
     VALUES (DEFAULT,$1,$2,$3,$4,$5,$6,$7,$8)`,
    [input.actorId || null, input.action, input.targetType, input.targetId || null, input.result, input.requestId || null, input.ip || null, JSON.stringify(input.metadata || {})],
  );
}

export async function createApiKey(userId: string, name: string, scopes: string[], expiresAt?: Date) {
  if (!pool) throw new Error("DATABASE_URL is not configured");
  const secret = `snck_${randomUUID().replace(/-/g, "")}${randomUUID().replace(/-/g, "")}`;
  const crypto = await import("node:crypto");
  const hash = crypto.createHash("sha256").update(secret).digest("hex");
  const id = randomUUID();
  await pool.query(`INSERT INTO api_keys (id,user_id,name,secret_hash,scopes,expires_at) VALUES ($1,$2,$3,$4,$5,$6)`, [id, userId, name, hash, JSON.stringify(scopes), expiresAt || null]);
  return { id, secret };
}
