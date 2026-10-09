import express from "express";
import crypto from "crypto";
import { requireAdmin } from "../middleware/auth.js";
import { readJSON, writeJSON } from "../services/db.js";

const router = express.Router();

router.use(requireAdmin);

// List API Keys
router.get("/", async (req, res) => {
  try {
    const apiKeys = await readJSON("api_keys.json") || [];
    // Omit key_hash when listing
    const keysWithoutHash = apiKeys.map((key: any) => ({
      id: key.id,
      label: key.label,
      scopes: key.scopes,
      created_by: key.created_by,
      created_at: key.created_at,
      expires_at: key.expires_at,
      last_used_at: key.last_used_at,
      revoked: key.revoked
    }));
    res.json(keysWithoutHash);
  } catch (err: any) {
    res.status(500).json({ error: "Internal Server Error" });
  }
});

// Create API Key
router.post("/", async (req, res) => {
  try {
    const { label, scopes, expires_at } = req.body;
    const user = (req as any).user;

    if (label !== undefined && (typeof label !== "string" || label.trim().length < 1 || label.length > 100)) {
      return res.status(400).json({ error: "Label must be a non-empty string of at most 100 characters" });
    }
    if (scopes !== undefined && (!Array.isArray(scopes) || scopes.length === 0 || scopes.length > 50 || !scopes.every((scope: unknown) => typeof scope === "string" && scope.length > 0 && scope.length <= 100))) {
      return res.status(400).json({ error: "Scopes must be a non-empty array of valid strings" });
    }
    let normalizedExpiry: string | null = null;
    if (expires_at !== undefined && expires_at !== null && expires_at !== "") {
      if (typeof expires_at !== "string" || !Number.isFinite(Date.parse(expires_at)) || Date.parse(expires_at) <= Date.now()) {
        return res.status(400).json({ error: "Expiry must be a valid future date" });
      }
      normalizedExpiry = new Date(expires_at).toISOString();
    }

    // Generate a high-entropy API key using cryptographically secure random bytes.
    const rawKey = crypto.randomBytes(32).toString("base64url");
    const keyString = `shironex-${rawKey}`;
    
    // Hash the key for storage
    const keyHash = crypto.createHash('sha256').update(keyString).digest('hex');

    const apiKeys = await readJSON("api_keys.json") || [];
    
    const newKey = {
      id: crypto.randomUUID(),
      key_hash: keyHash,
      label: label?.trim() || "Unnamed Key",
      scopes: scopes === undefined ? ["*"] : scopes,
      created_by: user.id,
      created_at: new Date().toISOString(),
      expires_at: normalizedExpiry,
      last_used_at: null,
      revoked: false
    };

    apiKeys.push(newKey);
    await writeJSON("api_keys.json", apiKeys);

    res.json({
      success: true,
      key: keyString, // Only show once
      id: newKey.id,
      label: newKey.label,
      scopes: newKey.scopes,
      expires_at: newKey.expires_at
    });
  } catch (err: any) {
    res.status(500).json({ error: "Internal Server Error" });
  }
});

// Delete API Key
router.delete("/:id", async (req, res) => {
  try {
    const { id } = req.params;
    const apiKeys = await readJSON("api_keys.json") || [];
    const keyIndex = apiKeys.findIndex((k: any) => k.id === id);
    
    if (keyIndex === -1) {
      return res.status(404).json({ error: "Key not found" });
    }

    apiKeys.splice(keyIndex, 1);
    await writeJSON("api_keys.json", apiKeys);

    res.json({ success: true, message: "Key deleted successfully" });
  } catch (err: any) {
    res.status(500).json({ error: "Internal Server Error" });
  }
});


export default router;
