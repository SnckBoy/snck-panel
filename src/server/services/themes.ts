import { readJSON, writeJSON } from "./db.js";

export type ThemeConfig = {
  primary: string; secondary: string; accent: string; background: string;
  surface: string; sidebar: string; navbar: string; text: string; muted: string;
  border: string; success: string; warning: string; error: string; radius: number;
};
export type ThemeRecord = {
  id: string; name: string; version: string; author: string; description: string;
  enabled: boolean; builtIn: boolean; protected: boolean; updatedAt: string; config: ThemeConfig;
};
export const defaultConfig: ThemeConfig = {
  primary: "#9275ff", secondary: "#5943b8", accent: "#b29cff", background: "#0a0a0f",
  surface: "#14141d", sidebar: "#09090e", navbar: "#111119", text: "#f4f3fa", muted: "#aaa7b8",
  border: "#302d3b", success: "#39d98a", warning: "#f0b24a", error: "#f26b78", radius: 16,
};
const make = (id: string, name: string, primary: string, background: string, surface: string, author = "Snck") => ({
  id, name, version: "1.0.0", author, description: `${name} color theme for Snck Panel.`, enabled: true,
  builtIn: true, protected: true, updatedAt: "2026-10-16T00:00:00.000Z",
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
const isHex = (value: unknown): value is string => typeof value === "string" && /^#[0-9a-fA-F]{6}$/.test(value);
export function validateConfig(value: any): value is ThemeConfig {
  if (!value || typeof value !== "object" || Array.isArray(value)) return false;
  const keys = Object.keys(defaultConfig) as (keyof ThemeConfig)[];
  if (Object.keys(value).length !== keys.length || Object.keys(value).some((key) => !keys.includes(key as keyof ThemeConfig))) return false;
  return keys.every((key) => key === "radius"
    ? typeof value[key] === "number" && Number.isFinite(value[key]) && value[key] >= 0 && value[key] <= 32
    : isHex(value[key]));
}
export function validateThemeInput(input: any) {
  const allowedFields = new Set(["id", "name", "version", "author", "description", "config", "supportedPanelVersion"]);
  if (!input || typeof input !== "object" || Array.isArray(input) || Object.keys(input).some((key) => !allowedFields.has(key))) throw new Error("Theme manifest contains unsupported fields.");
  const id = String(input?.id || "").toLowerCase();
  if (!/^[a-z0-9][a-z0-9-]{1,47}$/.test(id) || BUILT_INS.some((t) => t.id === id)) throw new Error("Theme ID must be 2–48 lowercase letters, numbers, or hyphens and cannot match a system theme.");
  const name = String(input?.name || "").trim();
  const author = String(input?.author || "Administrator").trim();
  const description = String(input?.description || "").trim();
  const version = String(input?.version || "1.0.0").trim();
  if (!name || name.length > 64 || !author || author.length > 64 || description.length > 240 || !/^\d+\.\d+\.\d+(?:[-+][a-zA-Z0-9.-]+)?$/.test(version)) throw new Error("Theme name, author, description, or semantic version is invalid.");
  if (!validateConfig(input.config)) throw new Error("Theme color configuration is invalid. Use six-digit hex colors and a radius from 0 to 32.");
  return { id, name, author, description, version, config: input.config as ThemeConfig };
}
export async function readThemes(): Promise<ThemeRecord[]> {
  let stored: any;
  try { stored = await readJSON("themes.json"); } catch { return [...BUILT_INS]; }
  if (!Array.isArray(stored)) return [...BUILT_INS];
  const custom = stored.filter((item: any) => item && typeof item.id === "string" && !BUILT_INS.some((base) => base.id === item.id) && validateConfig(item.config));
  return [...BUILT_INS.map((base) => {
    const prior = stored.find((item: any) => item?.id === base.id);
    return prior ? { ...base, enabled: prior.enabled !== false } : base;
  }), ...custom];
}
export async function saveThemes(themes: ThemeRecord[]) {
  await writeJSON("themes.json", themes.map((theme) => ({ ...theme, config: theme.config })));
}
export async function getActiveTheme() {
  const settings = await readJSON("settings.json") || {};
  const themes = await readThemes();
  let active = themes.find((theme) => theme.id === settings.snckThemeId && theme.enabled);
  if (!active) active = themes.find((theme) => theme.id === "snck-default") || BUILT_INS[0];
  return active;
}
