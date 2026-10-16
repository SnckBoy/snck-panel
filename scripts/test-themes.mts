import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import { DEFAULT_THEME_LAYOUT, isValidThemeLayout } from "../src/shared/themeLayout.ts";
import { BUILT_INS, defaultConfig, validateConfig, validateThemeInput } from "../src/server/services/themes.ts";

const valid = { id: "test-theme", name: "Test Theme", version: "1.0.0", author: "Test", description: "A safe sample theme", config: { ...defaultConfig } };
assert.equal(validateConfig(valid.config), true, "accepts a complete supported token set");
assert.equal(validateConfig({ ...defaultConfig, surfaceOpacity: 72 }), true, "accepts supported translucent glass surface opacity");
assert.equal(validateConfig({ ...defaultConfig, surfaceOpacity: 101 }), false, "rejects opacity above 100 percent");
assert.equal(validateConfig({ ...defaultConfig, surfaceOpacity: -1 }), false, "rejects negative opacity");
assert.equal(validateConfig({ ...defaultConfig, surfaceOpacity: 76.5 }), false, "rejects fractional opacity values");
assert.equal(validateConfig({ ...defaultConfig, primary: "url(javascript:alert(1))" }), false, "rejects CSS injection as a color");
assert.equal(validateConfig({ ...defaultConfig, radius: 1000 }), false, "rejects out-of-range radius");
assert.equal(validateConfig({ ...defaultConfig, customCss: "body { display:none }" }), false, "rejects unknown CSS/configuration fields");
assert.deepEqual(validateThemeInput(valid), { ...valid, layout: DEFAULT_THEME_LAYOUT }, "normalizes a valid theme and supplies a safe default layout");
const sample = JSON.parse(readFileSync(new URL("../examples/themes/ember-night/theme.json", import.meta.url), "utf8"));
assert.equal(validateThemeInput(sample).layout.dashboardOrder[1], "quick-actions", "sample package demonstrates an enabled safe dashboard widget");
const petroSample = JSON.parse(readFileSync(new URL("../examples/themes/snck-petro/theme.json", import.meta.url), "utf8"));
assert.equal(validateThemeInput(petroSample).config.surfaceOpacity, 76, "Snck Petro sample uses supported translucent surface opacity");
assert.equal(validateThemeInput(petroSample).layout.navigationOrder[1], "servers", "Snck Petro sample prioritizes the server list in navigation");
assert.equal(validateThemeInput(petroSample).layout.structure.shell, "pterodactyl", "Snck Petro switches the actual panel shell preset");
assert.equal(validateThemeInput(petroSample).layout.structure.dashboardMode, "server-first", "Snck Petro uses a server-first dashboard");
assert.equal(validateThemeInput(petroSample).layout.structure.serverNavigationOrder[0], "console", "Snck Petro prioritizes the server console");
assert.equal(validateThemeInput(petroSample).name, "Snck Petro", "sample theme is branded Snck Petro");
assert.equal(validateThemeInput(petroSample).layout.labels.servers, "Minecraft Servers", "theme file can rename existing navigation labels");
assert.equal(validateThemeInput(petroSample).layout.customNavigation[0].id, "custom-docs", "theme file can add safe custom shortcuts");
assert.equal(validateThemeInput(petroSample).layout.customNavigation.some((item) => item.target === "servers"), true, "theme file can add a working shortcut to an existing panel route");
const invalidCustomLink = { ...petroSample, id: "bad-custom-link", layout: { ...petroSample.layout, customNavigation: [{ id: "custom-unsafe", label: "Unsafe", url: "javascript:alert(1)" }] } };
assert.throws(() => validateThemeInput(invalidCustomLink), /layout/i, "rejects non-HTTPS custom links");
const invalidLabel = { ...petroSample, id: "bad-label", layout: { ...petroSample.layout, labels: { servers: "x".repeat(33) } } };
assert.throws(() => validateThemeInput(invalidLabel), /layout/i, "rejects excessively long menu labels");
const duplicateCustomLink = { ...petroSample, id: "duplicate-custom-link", layout: { ...petroSample.layout, customNavigation: [{ id: "custom-docs", label: "One", url: "https://example.com" }, { id: "custom-docs", label: "Two", url: "https://example.org" }] } };
assert.throws(() => validateThemeInput(duplicateCustomLink), /layout/i, "rejects duplicate custom shortcut IDs");
const unsafeStructure = { ...petroSample, id: "unsafe-layout", layout: { ...petroSample.layout, structure: { ...petroSample.layout.structure, serverNavigationOrder: ["console", "console"] } } };
assert.throws(() => validateThemeInput(unsafeStructure), /layout/i, "rejects duplicate server navigation entries");
const legacyConfig = { ...defaultConfig } as Record<string, unknown>;
delete legacyConfig.fontFamily; delete legacyConfig.fontScale; delete legacyConfig.density; delete legacyConfig.elevation;
assert.equal(validateThemeInput({ ...valid, config: legacyConfig }).config.fontFamily, "inter", "migrates older color-only theme packages");
assert.equal(isValidThemeLayout(DEFAULT_THEME_LAYOUT), true, "accepts the built-in safe layout catalog");
const customLayout = { ...DEFAULT_THEME_LAYOUT, dashboardOrder: ["quick-actions", "welcome", "metrics", "system-health", "server-inventory"], hiddenDashboard: ["system-health"] };
assert.deepEqual(validateThemeInput({ ...valid, layout: customLayout }).layout, customLayout, "accepts safe reorder and visibility choices");
for (const [description, input] of [
  ["path-like IDs", { ...valid, id: "../outside" }],
  ["system IDs", { ...valid, id: "snck-default" }],
  ["invalid names", { ...valid, name: "" }],
  ["invalid version", { ...valid, version: "latest" }],
  ["invalid tokens", { ...valid, config: { ...defaultConfig, accent: "red" } }],
  ["unsupported manifest fields", { ...valid, themeCss: "body { display:none }" }],
  ["unknown layout components", { ...valid, layout: { ...DEFAULT_THEME_LAYOUT, dashboardOrder: [...DEFAULT_THEME_LAYOUT.dashboardOrder, "script"] } }],
  ["duplicate layout items", { ...valid, layout: { ...DEFAULT_THEME_LAYOUT, navigationOrder: ["overview", ...DEFAULT_THEME_LAYOUT.navigationOrder] } }],
  ["hiding every navigation link", { ...valid, layout: { ...DEFAULT_THEME_LAYOUT, hiddenNavigation: [...DEFAULT_THEME_LAYOUT.navigationOrder] } }],
  ["hiding every public navigation link", { ...valid, layout: { ...DEFAULT_THEME_LAYOUT, hiddenNavigation: ["overview", "nodes", "servers", "settings"] } }],
] as const) assert.throws(() => validateThemeInput(input), undefined, `rejects ${description}`);
assert.equal(BUILT_INS.find((theme) => theme.id === "snck-default")?.protected, true, "Snck Default is protected");
assert.equal(BUILT_INS.find((theme) => theme.id === "snck-default")?.enabled, true, "Snck Default is enabled");
assert.equal(BUILT_INS.length >= 6, true, "offers multiple built-in presets");
console.log("Theme validation and layout tests passed.");
