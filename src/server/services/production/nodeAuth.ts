import crypto from "node:crypto";

export type NodeSignature = { nodeId: string; timestamp: number; nonce: string; signature: string };

export function signNodeRequest(secret: string, method: string, path: string, body: string, timestamp = Date.now(), nonce = crypto.randomBytes(16).toString("hex")) {
  const payload = `${timestamp}.${nonce}.${method.toUpperCase()}.${path}.${body}`;
  const signature = crypto.createHmac("sha256", secret).update(payload).digest("hex");
  return { timestamp, nonce, signature };
}

export function verifyNodeRequest(secret: string, method: string, path: string, body: string, auth: NodeSignature, maxSkewMs = 60_000) {
  if (!Number.isSafeInteger(auth.timestamp) || Math.abs(Date.now() - auth.timestamp) > maxSkewMs) return false;
  const expected = signNodeRequest(secret, method, path, body, auth.timestamp, auth.nonce).signature;
  const a = Buffer.from(expected, "hex");
  const b = Buffer.from(auth.signature || "", "hex");
  return a.length === b.length && crypto.timingSafeEqual(a, b);
}
