import express from "express";
import { readJSON } from "../services/db.js";

const router = express.Router();
import authRoutes from "./auth.js";
import serverRoutes from "./servers.js";
import systemRoutes from "./system.js";
import apiKeyRoutes from "./api-keys.js";
import nodeRoutes from "./nodes.js";
import nodeAgentRoutes from "./nodeAgent.js";
import allocationRoutes from "./allocations.js";
import cloudflareRoutes from "./cloudflare.js";

router.use("/auth", authRoutes);
router.use("/servers", serverRoutes);
router.use("/system", systemRoutes);
router.use("/admin/api-keys", apiKeyRoutes);
router.use("/nodes", nodeRoutes);
router.use("/node-agent", nodeAgentRoutes);
router.use("/allocations", allocationRoutes);
router.use("/cloudflare", cloudflareRoutes);

router.get("/settings", async (req, res) => {
  const settings = await readJSON("settings.json") || {};
  res.json({ 
    panelName: settings.panelName || "Snck",
    panelLogo: settings.panelLogo || "",
    panelBackgroundImage: settings.panelBackgroundImage || "",
    panelBackgroundBlur: settings.panelBackgroundBlur !== undefined ? settings.panelBackgroundBlur : 10,
    enablePlayit: settings.enablePlayit !== undefined ? settings.enablePlayit : false,
    enableTutorial: settings.enableTutorial !== undefined ? settings.enableTutorial : true,
    enableLoginAnimation: settings.enableLoginAnimation !== undefined ? settings.enableLoginAnimation : true,
    enableRegistration: settings.enableRegistration !== undefined ? settings.enableRegistration : true,
    theme: settings.theme || "aurora",
    appearance: settings.appearance || "dark",
    accent: settings.accent || "purple",
    backgroundEffect: settings.backgroundEffect || "aurora",
    reducedMotion: settings.reducedMotion !== undefined ? Boolean(settings.reducedMotion) : false,
    enableGoogleLogin: settings.enableGoogleLogin !== undefined ? settings.enableGoogleLogin : false,
    firebaseApiKey: settings.firebaseApiKey || "",
    firebaseAuthDomain: settings.firebaseAuthDomain || "",
    firebaseProjectId: settings.firebaseProjectId || "",
    firebaseStorageBucket: settings.firebaseStorageBucket || "",
    firebaseMessagingSenderId: settings.firebaseMessagingSenderId || "",
    firebaseAppId: settings.firebaseAppId || ""
  });
});


import axios from "axios";

type MarketplaceProvider = "modrinth" | "hangar" | "spiget";
const marketplaceUserAgent = "SnckBoy/SNCK-Panel/1.0 (Minecraft plugin marketplace)";
const asArray = (value: unknown): any[] => Array.isArray(value) ? value : [];
const asText = (value: unknown, fallback = "") => typeof value === "string" ? value : fallback;
const safeLimit = (value: unknown) => Math.min(40, Math.max(1, Number.parseInt(String(value || "24"), 10) || 24));
const normalizedLoader = (value: string) => {
  const lower = value.trim().toLowerCase();
  if (lower.includes("paper")) return "paper";
  if (lower.includes("purpur")) return "purpur";
  if (lower.includes("folia")) return "folia";
  if (lower.includes("spigot")) return "spigot";
  if (lower.includes("bukkit")) return "bukkit";
  return lower;
};
const marketplaceItem = (item: Record<string, any>) => ({
  id: String(item.id || ""),
  provider: item.provider as MarketplaceProvider,
  kind: "plugin" as const,
  name: asText(item.name, "Unnamed plugin"),
  description: asText(item.description),
  author: asText(item.author, "Unknown"),
  iconUrl: typeof item.iconUrl === "string" ? item.iconUrl : null,
  downloads: Math.max(0, Number(item.downloads) || 0),
  rating: Number.isFinite(Number(item.rating)) && item.rating !== null ? Number(item.rating) : null,
  latestVersion: typeof item.latestVersion === "string" ? item.latestVersion : null,
  gameVersions: asArray(item.gameVersions).map(String),
  loaders: asArray(item.loaders).map(String),
  platforms: asArray(item.platforms).map(String),
  updatedAt: typeof item.updatedAt === "string" ? item.updatedAt : null,
  projectUrl: asText(item.projectUrl),
  compatibility: (asArray(item.gameVersions).length || asArray(item.loaders).length) ? "known" as const : "unknown" as const,
});

async function searchModrinth(q: string, limit: number, sort: string, gameVersion: string, loader: string) {
  const facets: string[][] = [["project_type:plugin"]];
  if (gameVersion) facets.push(["versions:" + gameVersion]);
  const platform = normalizedLoader(loader);
  if (platform) facets.push(["categories:" + platform]);
  const index = sort === "updated" ? "updated" : "downloads";
  const response = await axios.get("https://api.modrinth.com/v2/search", {
    params: { query: q, facets: JSON.stringify(facets), index, limit },
    timeout: 12000,
    headers: { "User-Agent": marketplaceUserAgent },
  });
  return asArray(response.data?.hits).map((item: any) => marketplaceItem({
    id: item.project_id,
    provider: "modrinth",
    name: item.title,
    description: item.description,
    author: item.author,
    iconUrl: item.icon_url,
    downloads: item.downloads,
    rating: null,
    latestVersion: item.latest_version,
    gameVersions: gameVersion ? [gameVersion] : [],
    loaders: asArray(item.categories).filter((category: string) => ["paper", "purpur", "folia", "spigot", "bukkit"].includes(String(category).toLowerCase())),
    platforms: asArray(item.categories).filter((category: string) => ["paper", "purpur", "folia", "spigot", "bukkit"].includes(String(category).toLowerCase())),
    updatedAt: item.date_modified,
    projectUrl: "https://modrinth.com/plugin/" + (item.slug || item.project_id),
  }));
}

async function searchSpiget(q: string, limit: number) {
  const url = q ? "https://api.spiget.org/v2/search/resources/" + encodeURIComponent(q) : "https://api.spiget.org/v2/resources";
  const response = await axios.get(url, {
    params: { size: limit, sort: "-downloads", fields: "id,name,tag,description,downloads,updateDate,icon" },
    timeout: 12000,
    headers: { "User-Agent": marketplaceUserAgent },
  });
  return asArray(response.data).map((item: any) => marketplaceItem({
    id: String(item.id),
    provider: "spiget",
    name: item.name,
    description: item.tag || item.description,
    author: "Spigot resource",
    iconUrl: typeof item.icon?.url === "string" ? item.icon.url : null,
    downloads: item.downloads,
    rating: null,
    latestVersion: null,
    gameVersions: [],
    loaders: ["spigot", "paper", "purpur"],
    platforms: ["Spigot", "Paper", "Purpur"],
    updatedAt: item.updateDate ? new Date(item.updateDate * 1000).toISOString() : null,
    projectUrl: "https://www.spigotmc.org/resources/" + item.id + "/",
  }));
}

async function searchHangar(q: string, limit: number) {
  const response = await axios.get("https://hangar.papermc.io/api/v1/projects", {
    params: { query: q || undefined, limit, offset: 0, sort: "-downloads" },
    timeout: 12000,
    headers: { "User-Agent": marketplaceUserAgent },
  });
  const rows = asArray(response.data?.result || response.data?.projects || response.data);
  return rows.map((item: any) => {
    const owner = asText(item.namespace?.owner, asText(item.owner?.name, asText(item.owner)));
    const slug = asText(item.namespace?.slug, asText(item.slug, asText(item.name)));
    return marketplaceItem({
      id: owner && slug ? owner + "/" + slug : slug,
      provider: "hangar",
      name: item.name,
      description: item.description,
      author: owner || "Hangar author",
      iconUrl: item.avatarUrl || item.avatar,
      downloads: item.stats?.downloads ?? item.downloads,
      rating: null,
      latestVersion: null,
      gameVersions: [],
      loaders: ["paper", "velocity"],
      platforms: ["Paper", "Velocity"],
      updatedAt: item.lastUpdated,
      projectUrl: "https://hangar.papermc.io/" + owner + "/" + slug,
    });
  }).filter((item: any) => item.id && item.name);
}

router.get("/marketplace/filters", async (_req, res) => {
  try {
    const response = await axios.get("https://api.modrinth.com/v2/tag/game_version", {
      timeout: 10000,
      headers: { "User-Agent": marketplaceUserAgent },
    });
    const versions = asArray(response.data)
      .filter((item: any) => item.version && (item.version_type === "release" || !item.version_type))
      .map((item: any) => String(item.version))
      .filter((version: string, index: number, all: string[]) => all.indexOf(version) === index);
    return res.json({ versions });
  } catch {
    return res.status(502).json({ error: "Unable to load Minecraft versions from Modrinth." });
  }
});

router.get("/marketplace/search", async (req, res) => {
  const q = String(req.query.q || "").trim().slice(0, 120);
  const provider = String(req.query.provider || "all").toLowerCase();
  const sort = String(req.query.sort || "downloads").toLowerCase();
  const gameVersion = String(req.query.gameVersion || "").trim().slice(0, 20);
  const loader = String(req.query.loader || "").trim().slice(0, 30);
  const limit = safeLimit(req.query.limit);
  const providers: MarketplaceProvider[] = provider === "all"
    ? ["modrinth", "hangar", "spiget"]
    : provider === "modrinth" || provider === "hangar" || provider === "spiget" ? [provider] : [];
  if (!providers.length) return res.status(400).json({ error: "Unknown marketplace provider" });

  const searches = await Promise.allSettled(providers.map(async (source) => {
    if (source === "modrinth") return searchModrinth(q, limit, sort, gameVersion, loader);
    if (source === "hangar") return searchHangar(q, limit);
    return searchSpiget(q, limit);
  }));
  const items = searches.flatMap((result) => result.status === "fulfilled" ? result.value : []);
  const failedProviders = searches.flatMap((result, index) => result.status === "rejected" ? [providers[index]] : []);
  const deduped = new Map<string, ReturnType<typeof marketplaceItem>>();
  for (const item of items) {
    if (!item.id || !item.name) continue;
    deduped.set(item.provider + ":" + item.id, item);
  }
  const results = Array.from(deduped.values()).sort((a, b) => {
    if (sort === "name") return a.name.localeCompare(b.name);
    if (sort === "updated") return String(b.updatedAt || "").localeCompare(String(a.updatedAt || ""));
    return b.downloads - a.downloads;
  }).slice(0, limit);
  if (!results.length && failedProviders.length === providers.length) {
    return res.status(502).json({ error: "Plugin providers could not be reached. Check the VPS network/DNS and try again." });
  }
  return res.json({ items: results, total: results.length, failedProviders, query: q, featured: !q });
});

export default router;
