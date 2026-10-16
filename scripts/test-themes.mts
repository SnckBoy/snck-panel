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
const glassSample = JSON.parse(readFileSync(new URL("../examples/themes/pterodactyl-glass/theme.json", import.meta.url), "utf8"));
assert.equal(validateThemeInput(glassSample).config.surfaceOpacity, 76, "Pterodactyl Glass sample uses supported translucent surface opacity");
assert.equal(validateThemeInput(glassSample).layout.navigationOrder[1], "servers", "Pterodactyl Glass sample prioritizes the server list in navigation");
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
