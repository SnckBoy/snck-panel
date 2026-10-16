import assert from "node:assert/strict";
import { BUILT_INS, defaultConfig, validateConfig, validateThemeInput } from "../src/server/services/themes.ts";

const valid = { id: "test-theme", name: "Test Theme", version: "1.0.0", author: "Test", description: "A safe sample theme", config: { ...defaultConfig } };
assert.equal(validateConfig(valid.config), true, "accepts a complete supported token set");
assert.equal(validateConfig({ ...defaultConfig, primary: "url(javascript:alert(1))" }), false, "rejects CSS injection as a color");
assert.equal(validateConfig({ ...defaultConfig, radius: 1000 }), false, "rejects out-of-range radius");
assert.equal(validateConfig({ ...defaultConfig, customCss: "body { display:none }" }), false, "rejects unknown CSS/configuration fields");
assert.deepEqual(validateThemeInput(valid), valid, "normalizes a valid theme manifest");
for (const [description, input] of [
  ["path-like IDs", { ...valid, id: "../outside" }],
  ["system IDs", { ...valid, id: "snck-default" }],
  ["invalid names", { ...valid, name: "" }],
  ["invalid version", { ...valid, version: "latest" }],
  ["invalid tokens", { ...valid, config: { ...defaultConfig, accent: "red" } }],
  ["unsupported manifest fields", { ...valid, themeCss: "body { display:none }" }],
] as const) assert.throws(() => validateThemeInput(input), undefined, `rejects ${description}`);
assert.equal(BUILT_INS.find((theme) => theme.id === "snck-default")?.protected, true, "Snck Default is protected");
assert.equal(BUILT_INS.find((theme) => theme.id === "snck-default")?.enabled, true, "Snck Default is enabled");
assert.equal(BUILT_INS.length >= 6, true, "offers multiple built-in presets");
console.log("Theme validation tests passed (14 assertions).");
