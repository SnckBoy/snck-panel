import assert from "node:assert/strict";
import fs from "node:fs";
import os from "node:os";
import path from "node:path";
import test from "node:test";
import { jailedPath, safeServerRoot, sftpOpenFlagsToNode } from "./sftp.js";

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


test("SFTP open flags do not grant write access to read-only handles", () => {
  const flags = sftpOpenFlagsToNode(0x00000001);
  assert.equal((flags & (fs.constants.O_WRONLY | fs.constants.O_RDWR)), fs.constants.O_RDONLY);
});

test("SFTP open flags preserve create, truncate, append and exclusive semantics", () => {
  const flags = sftpOpenFlagsToNode(0x00000002 | 0x00000004 | 0x00000008 | 0x00000010 | 0x00000020);
  assert.notEqual(flags & fs.constants.O_WRONLY, 0);
  assert.notEqual(flags & fs.constants.O_APPEND, 0);
  assert.notEqual(flags & fs.constants.O_CREAT, 0);
  assert.notEqual(flags & fs.constants.O_TRUNC, 0);
  assert.notEqual(flags & fs.constants.O_EXCL, 0);
});

test("SFTP open flags reject requests without read or write permission", () => {
  assert.throws(() => sftpOpenFlagsToNode(0), /read or write/);
});
