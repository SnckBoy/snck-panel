type Bucket = { count: number; resetAt: number };
const buckets = new Map<string, Bucket>();

export function rateLimit(key: string, limit: number, windowMs: number) {
  const now = Date.now();
  const current = buckets.get(key);
  if (!current || current.resetAt <= now) {
    buckets.set(key, { count: 1, resetAt: now + windowMs });
    return { allowed: true, remaining: Math.max(0, limit - 1), retryAfterMs: 0 };
  }
  current.count += 1;
  if (current.count > limit) return { allowed: false, remaining: 0, retryAfterMs: current.resetAt - now };
  return { allowed: true, remaining: limit - current.count, retryAfterMs: 0 };
}

export function rateLimitMiddleware(limit: number, windowMs: number, keyer: (req: any) => string = (req) => req.ip || "unknown") {
  return (req: any, res: any, next: any) => {
    const result = rateLimit(`${keyer(req)}:${req.path}`, limit, windowMs);
    res.setHeader("X-RateLimit-Remaining", result.remaining);
    if (!result.allowed) {
      res.setHeader("Retry-After", Math.ceil(result.retryAfterMs / 1000));
      return res.status(429).json({ success: false, error: { code: "RATE_LIMITED", message: "Too many requests" } });
    }
    next();
  };
}
