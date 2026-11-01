import assert from "node:assert/strict";
import fs from "node:fs";
import os from "node:os";
import path from "node:path";
import test from "node:test";
import { jailedPath, safeServerRoot } from "./sftp.js";

test("SFTP jail accepts a server-relative file and rejects traversal", () => {
  const base = fs.mkdtempSync(path.join(os.tmpdir(), "snck-sftp-"));
  const root = safeServerRoot(base, "server-1");
  fs.writeFileSync(path.join(root, "ok.txt"), "ok");
  assert.equal(jailedPath(root, "ok.txt"), path.join(root, "ok.txt"));
  assert.throws(() => jailedPath(root, "../outside.txt"), /escapes/);
  fs.rmSync(base, { recursive: true, force: true });
});

test("SFTP jail rejects symlink escapes", () => {
  const base = fs.mkdtempSync(path.join(os.tmpdir(), "snck-sftp-"));
  const root = safeServerRoot(base, "server-2");
  const outside = path.join(base, "outside");
  fs.mkdirSync(outside);
  fs.symlinkSync(outside, path.join(root, "escape"));
  assert.throws(() => jailedPath(root, "escape/secret.txt"), /Symlink escapes/);
  fs.rmSync(base, { recursive: true, force: true });
});
