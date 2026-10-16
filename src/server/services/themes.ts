import { readJSON, writeJSON } from "./db.js";
import { DEFAULT_THEME_LAYOUT, isValidThemeLayout, normalizeThemeLayout, type ThemeLayout } from "../../shared/themeLayout.js";

export type ThemeConfig = {
  primary: string; secondary: string; accent: string; background: string;
  surface: string; sidebar: string; navbar: string; text: string; muted: string;
  border: string; success: string; warning: string; error: string; radius: number;
  fontFamily: "system" | "inter" | "mono"; fontScale: number;
  density: "compact" | "comfortable" | "spacious"; elevation: "flat" | "soft" | "glow"; surfaceOpacity: number;
};
export type ThemeRecord = {
  id: string; name: string; version: string; author: string; description: string;
  enabled: boolean; builtIn: boolean; protected: boolean; updatedAt: string;
  config: ThemeConfig; layout: ThemeLayout;
};
export const defaultConfig: ThemeConfig = {
  primary: "#9275ff", secondary: "#5943b8", accent: "#b29cff", background: "#0a0a0f",
  surface: "#14141d", sidebar: "#09090e", navbar: "#111119", text: "#f4f3fa", muted: "#aaa7b8",
  border: "#302d3b", success: "#39d98a", warning: "#f0b24a", error: "#f26b78", radius: 16,
  fontFamily: "inter", fontScale: 1, density: "comfortable", elevation: "soft", surfaceOpacity: 100,
};
const make = (id: string, name: string, primary: string, background: string, surface: string, author = "Snck"): ThemeRecord => ({
  id, name, version: "1.0.0", author, description: `${name} color theme for Snck Panel.`, enabled: true,
  builtIn: true, protected: true, updatedAt: "2026-10-16T00:00:00.000Z", layout: { ...DEFAULT_THEME_LAYOUT },
  config: { ...defaultConfig, primary, background, surface, secondary: primary, accent: primary, sidebar: background, navbar: surface },
});
export const BUILT_INS: ThemeRecord[] = [
  make("snck-default", "Snck Default", "#9275ff", "#0a0a0f", "#14141d"),
  make("midnight", "Midnight", "#7099d8", "#080e1a", "#111a2a"),
  make("royal-purple", "Royal Purple", "#a481da", "#160d25", "#28183e"),
  make("nebula", "Nebula", "#9a7ceb", "#120d1b", "#20182e"),
  make("cyber-blue", "Cyber Blue", "#57b5ba", "#071315", "#102326"),
  make("aurora", "Aurora", "#7c6af0", "#0a0a0f", "#111117"),
  make("obsidian", "Obsidian", "#b8b8c2", "#08080d", "#111117"),
];
const colorKeys = ["primary", "secondary", "accent", "background", "surface", "sidebar", "navbar", "text", "muted", "border", "success", "warning", "error"] as const;
const isHex = (value: unknown): value is string => typeof value === "string" && /^#[0-9a-fA-F]{6}$/.test(value);
export function validateConfig(value: any): value is ThemeConfig {
  if (!value || typeof value !== "object" || Array.isArray(value)) return false;
  const keys = Object.keys(defaultConfig) as (keyof ThemeConfig)[];
  if (Object.keys(value).length !== keys.length || Object.keys(value).some((key) => !keys.includes(key as keyof ThemeConfig))) return false;
  return colorKeys.every((key) => isHex(value[key])) &&
    Number.isFinite(value.radius) && value.radius >= 0 && value.radius <= 32 &&
    ["system", "inter", "mono"].includes(value.fontFamily) &&
    Number.isFinite(value.fontScale) && value.fontScale >= 0.85 && value.fontScale <= 1.25 &&
    ["compact", "comfortable", "spacious"].includes(value.density) &&
    ["flat", "soft", "glow"].includes(value.elevation) &&
    Number.isFinite(value.surfaceOpacity) && value.surfaceOpacity >= 55 && value.surfaceOpacity <= 100;
}
function normalizeConfig(value: any): ThemeConfig | null {
  if (!value || typeof value !== "object" || Array.isArray(value)) return null;
  const keys = Object.keys(defaultConfig);
  if (Object.keys(value).some((key) => !keys.includes(key))) return null;
  const merged = { ...defaultConfig, ...value };
  return validateConfig(merged) ? merged : null;
}
export function validateThemeInput(input: any) {
  const allowedFields = new Set(["id", "name", "version", "author", "description", "config", "layout", "supportedPanelVersion"]);
  if (!input || typeof input !== "object" || Array.isArray(input) || Object.keys(input).some((key) => !allowedFields.has(key))) throw new Error("Theme manifest contains unsupported fields.");
  const id = String(input?.id || "").toLowerCase();
  if (!/^[a-z0-9][a-z0-9-]{1,47}$/.test(id) || BUILT_INS.some((t) => t.id === id)) throw new Error("Theme ID must be 2–48 lowercase letters, numbers, or hyphens and cannot match a system theme.");
  const name = String(input?.name || "").trim();
  const author = String(input?.author || "Administrator").trim();
  const description = String(input?.description || "").trim();
  const version = String(input?.version || "1.0.0").trim();
  if (!name || name.length > 64 || !author || author.length > 64 || description.length > 240 || !/^\d+\.\d+\.\d+(?:[-+][a-zA-Z0-9.-]+)?$/.test(version)) throw new Error("Theme name, author, description, or semantic version is invalid.");
  const config = normalizeConfig(input.config);
  if (!config) throw new Error("Theme configuration is invalid. Use supported hex colors, typography, spacing, and elevation values.");
  if (input.layout !== undefined && !isValidThemeLayout(input.layout)) throw new Error("Theme layout contains unsupported, duplicate, or invalid navigation/dashboard items.");
  return { id, name, author, description, version, config, layout: normalizeThemeLayout(input.layout) };
}
export async function readThemes(): Promise<ThemeRecord[]> {
  let stored: any;
  try { stored = await readJSON("themes.json"); } catch { return [...BUILT_INS]; }
  if (!Array.isArray(stored)) return [...BUILT_INS];
  const custom: ThemeRecord[] = stored.flatMap((item: any) => {
    if (!item || typeof item.id !== "string" || BUILT_INS.some((base) => base.id === item.id)) return [];
    const config = normalizeConfig(item.config);
    if (!config) return [];
    return [{ ...item, config, layout: normalizeThemeLayout(item.layout) } as ThemeRecord];
  });
  return [...BUILT_INS.map((base) => {
    const prior = stored.find((item: any) => item?.id === base.id);
    return prior ? { ...base, enabled: prior.enabled !== false } : base;
  }), ...custom];
}
export async function saveThemes(themes: ThemeRecord[]) {
  await writeJSON("themes.json", themes.map((theme) => ({ ...theme, config: theme.config, layout: normalizeThemeLayout(theme.layout) })));
}
export async function getActiveTheme() {
  const settings = await readJSON("settings.json") || {};
  const themes = await readThemes();
  let active = themes.find((theme) => theme.id === settings.snckThemeId && theme.enabled);
  if (!active) active = themes.find((theme) => theme.id === "snck-default") || BUILT_INS[0];
  return active;
}
