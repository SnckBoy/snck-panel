import assert from "node:assert/strict";
import fs from "fs-extra";
import path from "node:path";
import { updateJSON } from "../src/server/services/db.js";

const store = path.resolve(process.cwd(), ".data", "atomic-json-test.json");
await fs.ensureDir(path.dirname(store));
await fs.remove(store);
try {
  await Promise.all(
    Array.from({ length: 100 }, () =>
      updateJSON<number[]>("atomic-json-test.json", (current) => [...current, current.length], []),
    ),
  );
  const result = (await fs.readJson(store)) as number[];
  assert.equal(result.length, 100, "concurrent updates must not overwrite one another");
  assert.deepEqual(
    [...result].sort((a, b) => a - b),
    Array.from({ length: 100 }, (_, i) => i),
  );
  console.log("atomic JSON update test passed (100 concurrent updates)");
} finally {
  await fs.remove(store);
}
