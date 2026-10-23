import crypto from "crypto";
import { readJSON, writeJSON } from "./db.js";

// Centralized JWT secret accessor. There is no hardcoded fallback: a predictable
// default secret would let anyone forge admin tokens for any SNCK install that
// forgot to set one. The installer generates this value in .env.
export const getJwtSecret = () => {
  const secret = process.env.JWT_SECRET;
  if (!secret || secret.length < 16) {
    throw new Error(
      "JWT_SECRET is not set (or too short). Set a long random value in .env, e.g.: " +
      "JWT_SECRET=$(openssl rand -hex 32)"
    );
  }
  return secret;
};

const keyMaterial = () => {
  const raw = process.env.NODE_ENCRYPTION_KEY || "";
  if (!raw) throw new Error("NODE_ENCRYPTION_KEY is required for encrypted infrastructure secrets");
  return crypto.createHash("sha256").update(raw).digest();
};

export const encryptSecret = (value: string) => {
  const iv = crypto.randomBytes(12), key = keyMaterial();
  const cipher = crypto.createCipheriv("aes-256-gcm", key, iv);
  const encrypted = Buffer.concat([cipher.update(value, "utf8"), cipher.final()]);
  return `${iv.toString("base64url")}.${cipher.getAuthTag().toString("base64url")}.${encrypted.toString("base64url")}`;
};
export const decryptSecret = (value: string) => {
  const [ivB64, tagB64, dataB64] = value.split(".");
  if (!ivB64 || !tagB64 || !dataB64) throw new Error("Invalid encrypted secret format");
  const decipher = crypto.createDecipheriv("aes-256-gcm", keyMaterial(), Buffer.from(ivB64, "base64url"));
  decipher.setAuthTag(Buffer.from(tagB64, "base64url"));
  return Buffer.concat([decipher.update(Buffer.from(dataB64, "base64url")), decipher.final()]).toString("utf8");
};
export const hashSecret = (value: string) => crypto.createHash("sha256").update(value).digest("hex");
export const randomSecret = (bytes = 32) => crypto.randomBytes(bytes).toString("base64url");

// Keep the policy easy to use while preventing empty and trivially short passwords.
// Passwords are stored only as bcrypt hashes; complexity is not a substitute for hashing.
export const isStrongPassword = (password: unknown): password is string =>
  typeof password === "string" && password.length >= 8 && password.length <= 256;

export const audit = async (action: string, req: any, metadata: any = {}) => {
  const logs = await readJSON("audit_logs.json") || [];
  logs.push({
    id: crypto.randomUUID(), action, actorId: req?.user?.id || "node",
    ip: req?.ip, createdAt: new Date().toISOString(), metadata
  });
  await writeJSON("audit_logs.json", logs.slice(-5000));
};

type RateLimitBucket = { count: number; reset: number };
const buckets = new Map<string, RateLimitBucket>();
let lastSweepAt = 0;
const MAX_RATE_LIMIT_BUCKETS = 50_000;

function sweepExpiredBuckets(now: number) {
  if (now - lastSweepAt < 60_000) return;
  lastSweepAt = now;
  for (const [key, bucket] of buckets) {
    if (bucket.reset <= now) buckets.delete(key);
  }
}

export const rateLimit = (limit = 120, windowMs = 60_000) => (req: any, res: any, next: any) => {
  const now = Date.now();
  sweepExpiredBuckets(now);
  const key = `${req.ip || req.socket?.remoteAddress || "unknown"}:${req.path}`;
  let bucket = buckets.get(key);

  if (!bucket || bucket.reset <= now) {
    if (buckets.size >= MAX_RATE_LIMIT_BUCKETS) {
      // Do not grow memory without bound if requests arrive from many unique IPs.
      return res.status(429).json({ error: "Rate limit capacity reached; retry shortly." });
    }
    bucket = { count: 0, reset: now + Math.max(1_000, windowMs) };
    buckets.set(key, bucket);
  }

  bucket.count += 1;
  if (bucket.count > Math.max(1, limit)) {
    res.setHeader("Retry-After", String(Math.max(1, Math.ceil((bucket.reset - now) / 1000))));
    return res.status(429).json({ error: "Too many requests" });
  }
  next();
};
