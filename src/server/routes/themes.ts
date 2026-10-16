import express from "express";
import multer from "multer";
import yauzl from "yauzl";
import { PassThrough } from "stream";
import { ZipArchive } from "archiver";
import { requireAdmin } from "../middleware/auth.js";
import { readJSON, writeJSON } from "../services/db.js";
import { getActiveTheme, readThemes, saveThemes, validateThemeInput, type ThemeRecord } from "../services/themes.js";
import { rateLimitMiddleware } from "../services/production/rateLimit.js";
const router = express.Router();
const upload = multer({ storage: multer.memoryStorage(), limits: { fileSize: 2 * 1024 * 1024, files: 1 } });
router.use(requireAdmin, rateLimitMiddleware(30, 60_000));
const clean = (theme: ThemeRecord) => theme;
router.get("/", async (_req, res) => res.json({ themes: (await readThemes()).map(clean), activeId: (await getActiveTheme()).id }));
router.post("/", async (req, res) => {
  try {
    const data = validateThemeInput(req.body);
    const themes = await readThemes();
    if (themes.some((theme) => theme.id === data.id)) return res.status(409).json({ error: "A theme with that ID already exists." });
    const created: ThemeRecord = { ...data, enabled: true, builtIn: false, protected: false, updatedAt: new Date().toISOString() };
    themes.push(created);
    await saveThemes(themes);
    res.status(201).json({ theme: created });
  } catch (error: any) { res.status(400).json({ error: error.message || "Theme could not be saved." }); }
});
router.put("/:id", async (req, res) => {
  try {
    const data = validateThemeInput({ ...req.body, id: req.params.id });
    const themes = await readThemes();
    const index = themes.findIndex((theme) => theme.id === data.id);
    if (index < 0 || themes[index].builtIn) return res.status(404).json({ error: "Custom theme not found or cannot be edited." });
    themes[index] = { ...themes[index], ...data, updatedAt: new Date().toISOString() };
    await saveThemes(themes);
    res.json({ theme: themes[index] });
  } catch (error: any) { res.status(400).json({ error: error.message || "Theme could not be updated." }); }
});
router.patch("/:id/enabled", async (req, res) => {
  const themes = await readThemes(); const theme = themes.find((item) => item.id === req.params.id);
  if (!theme) return res.status(404).json({ error: "Theme not found." });
  if (typeof req.body?.enabled !== "boolean") return res.status(400).json({ error: "enabled must be a boolean." });
  if (theme.id === "snck-default" && !req.body.enabled) return res.status(400).json({ error: "Snck Default must remain enabled." });
  theme.enabled = req.body.enabled;
  if (!theme.enabled) {
    const settings = await readJSON("settings.json") || {};
    if (settings.snckThemeId === theme.id) { settings.snckThemeId = "snck-default"; await writeJSON("settings.json", settings); }
  }
  await saveThemes(themes); req.app.get("io")?.emit("settings_updated"); res.json({ theme });
});
router.post("/:id/apply", async (req, res) => {
  const themes = await readThemes(); const theme = themes.find((item) => item.id === req.params.id);
  if (!theme || !theme.enabled) return res.status(400).json({ error: "Theme is unavailable or disabled." });
  const settings = await readJSON("settings.json") || {};
  settings.snckThemeId = theme.id;
  await writeJSON("settings.json", settings);
  req.app.get("io")?.emit("settings_updated"); res.json({ activeId: theme.id });
});
router.post("/restore-default", async (req, res) => {
  const settings = await readJSON("settings.json") || {};
  settings.snckThemeId = "snck-default";
  await writeJSON("settings.json", settings);
  req.app.get("io")?.emit("settings_updated"); res.json({ activeId: "snck-default" });
});
router.delete("/:id", async (req, res) => {
  const themes = await readThemes(); const theme = themes.find((item) => item.id === req.params.id);
  if (!theme) return res.status(404).json({ error: "Theme not found." });
  if (theme.protected || theme.builtIn) return res.status(403).json({ error: "System themes are protected." });
  const next = themes.filter((item) => item.id !== theme.id);
  const settings = await readJSON("settings.json") || {};
  if (settings.snckThemeId === theme.id) { settings.snckThemeId = "snck-default"; await writeJSON("settings.json", settings); }
  await saveThemes(next); req.app.get("io")?.emit("settings_updated"); res.json({ success: true });
});
router.get("/:id/export", async (req, res) => {
  const theme = (await readThemes()).find((item) => item.id === req.params.id);
  if (!theme) return res.status(404).json({ error: "Theme not found." });
  res.setHeader("Content-Type", "application/zip");
  res.setHeader("Content-Disposition", `attachment; filename="${theme.id}.zip"`);
  const output = new PassThrough(); output.pipe(res);
  const archive = new ZipArchive({ zlib: { level: 9 } });
  archive.on("error", () => { if (!res.headersSent) res.status(500); res.end(); });
  archive.pipe(output);
  archive.append(JSON.stringify({ id: theme.id, name: theme.name, version: theme.version, author: theme.author, description: theme.description, supportedPanelVersion: ">=0.2.0", config: theme.config, layout: theme.layout }, null, 2), { name: `${theme.id}/theme.json` });
  await archive.finalize();
});
function readManifest(buffer: Buffer): Promise<any> {
  return new Promise((resolve, reject) => {
    yauzl.fromBuffer(buffer, { lazyEntries: true, validateEntrySizes: true }, (error, zip) => {
      if (error || !zip) return reject(new Error("Invalid ZIP archive."));
      let manifest: any = null; let count = 0; let rejected = false;
      zip.on("entry", (entry) => {
        count += 1;
        const name = entry.fileName.replace(/\\/g, "/");
        if (count > 100 || name.startsWith("/") || name.split("/").some((part: string) => part === "..") || /^[a-zA-Z]:/.test(name)) {
          rejected = true; zip.close(); return reject(new Error("Unsafe or oversized theme archive."));
        }
        if (name.endsWith("/")) { zip.readEntry(); return; }
        if (name.split("/").pop() !== "theme.json") { rejected = true; zip.close(); return reject(new Error("Only theme.json manifests are supported; assets, styles, and scripts are not accepted.")); }
        if (entry.uncompressedSize > 32 * 1024) { rejected = true; zip.close(); return reject(new Error("Theme manifest is too large.")); }
        zip.openReadStream(entry, (streamError, stream) => {
          if (streamError || !stream) { zip.close(); return reject(new Error("Could not read theme manifest.")); }
          const chunks: Buffer[] = []; let bytes = 0;
          stream.on("data", (chunk: Buffer) => { bytes += chunk.length; if (bytes > 32 * 1024) { rejected = true; stream.destroy(new Error("Manifest too large")); } else chunks.push(chunk); });
          stream.on("error", () => { zip.close(); reject(new Error("Invalid theme manifest.")); });
          stream.on("end", () => {
            if (rejected) return;
            try { if (manifest) throw new Error("Archive must contain one theme.json."); manifest = JSON.parse(Buffer.concat(chunks).toString("utf8")); zip.readEntry(); }
            catch (parseError) { zip.close(); reject(parseError); }
          });
        });
      });
      zip.on("end", () => manifest ? resolve(manifest) : reject(new Error("ZIP must contain theme.json.")));
      zip.on("error", () => reject(new Error("Invalid ZIP archive.")));
      zip.readEntry();
    });
  });
}
router.post("/import", upload.single("package"), async (req, res) => {
  if (!req.file || !req.file.originalname.toLowerCase().endsWith(".zip")) return res.status(400).json({ error: "Upload a ZIP theme package (maximum 2 MB)." });
  try {
    const manifest = await readManifest(req.file.buffer);
    if (typeof manifest.supportedPanelVersion !== "string" || !/^\s*(?:\^|>=)?\s*0\.2(?:\.\d+)?(?:\s|$)/.test(manifest.supportedPanelVersion)) throw new Error("Theme package does not declare compatibility with this Snck Panel version.");
    const data = validateThemeInput(manifest);
    const themes = await readThemes();
    if (themes.some((theme) => theme.id === data.id)) return res.status(409).json({ error: "A theme with that ID already exists." });
    const created: ThemeRecord = { ...data, enabled: true, builtIn: false, protected: false, updatedAt: new Date().toISOString() };
    themes.push(created); await saveThemes(themes); res.status(201).json({ theme: created, message: "Validated declarative theme installed." });
  } catch (error: any) { res.status(400).json({ error: error.message || "Theme package rejected." }); }
});
export default router;
