import assert from "node:assert/strict";
import fs from "node:fs";
import os from "node:os";
import path from "node:path";
import test from "node:test";
import { jailedPath, safeServerRoot, sftpOpenFlagsToNode } from "./sftp.js";

const waitForEvent = (emitter: any, event: string): Promise<void> => new Promise((resolve, reject) => {
  emitter.once(event, () => resolve());
  emitter.once("error", reject);
});

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
  assert.throws(() => jailedPath(root, "escape/secret.txt"), /Symlink/);
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


test("SSH/SFTP protocol uploads and downloads files inside the authenticated server jail", async () => {
  const { createServer } = await import("node:http");
  const { generateKeyPairSync } = await import("node:crypto");
  const { Client } = await import("ssh2");
  const { startSftpServer } = await import("./sftp.js");

  const base = fs.mkdtempSync(path.join(os.tmpdir(), "snck-sftp-protocol-"));
  const hostKeyPath = path.join(base, "host.key");
  const keys = generateKeyPairSync("rsa", {
    modulusLength: 2048,
    privateKeyEncoding: { type: "pkcs1", format: "pem" },
    publicKeyEncoding: { type: "spki", format: "pem" },
  });
  fs.writeFileSync(hostKeyPath, keys.privateKey, { mode: 0o600 });

  const panel = createServer((request, response) => {
    if (request.url !== "/api/node-agent/sftp-auth") {
      response.writeHead(404).end();
      return;
    }
    let body = "";
    request.setEncoding("utf8");
    request.on("data", chunk => { body += chunk; });
    request.on("end", () => {
      const payload = JSON.parse(body);
      const authorized = request.headers.authorization === "Bearer node-secret"
        && payload.nodeId === "node-1"
        && payload.username === "server-user"
        && payload.password === "server-password";
      response.writeHead(authorized ? 200 : 401, { "content-type": "application/json" });
      response.end(JSON.stringify(authorized ? { authenticated: true, serverId: "server-1" } : { error: "unauthorized" }));
    });
  });
  panel.listen(0, "127.0.0.1");
  await waitForEvent(panel, "listening");
  const panelAddress = panel.address();
  assert.ok(panelAddress && typeof panelAddress !== "string");

  const server = startSftpServer({
    host: "127.0.0.1",
    port: 0,
    hostKey: hostKeyPath,
    panelUrl: `http://127.0.0.1:${panelAddress.port}`,
    nodeId: "node-1",
    credential: "node-secret",
    serversDir: path.join(base, "servers"),
  });
  await waitForEvent(server, "listening");
  const serverAddress = server.address();
  assert.ok(serverAddress && typeof serverAddress !== "string");

  const localSource = path.join(base, "source.txt");
  const localDownload = path.join(base, "download.txt");
  fs.writeFileSync(localSource, "real SFTP protocol works");
  const client = new Client();

  try {
    await new Promise<void>((resolve, reject) => {
      client.once("ready", resolve);
      client.once("error", reject);
      client.connect({
        host: "127.0.0.1",
        port: serverAddress.port,
        username: "server-user",
        password: "server-password",
        hostVerifier: () => true,
        readyTimeout: 5000,
      });
    });

    const sftp = await new Promise<any>((resolve, reject) => {
      client.sftp((error, channel) => error ? reject(error) : resolve(channel));
    });
    await new Promise<void>((resolve, reject) => sftp.fastPut(localSource, "uploaded.txt", (error: Error | undefined) => error ? reject(error) : resolve()));
    await new Promise<void>((resolve, reject) => sftp.fastGet("/uploaded.txt", localDownload, (error: Error | undefined) => error ? reject(error) : resolve()));
    const entries = await new Promise<any[]>((resolve, reject) => sftp.readdir("/", (error: Error | undefined, files: any[]) => error ? reject(error) : resolve(files)));
    assert.ok(entries.some(entry => entry.filename === "uploaded.txt"));
    await new Promise<void>(resolve => sftp.stat("../outside.txt", (error: Error | undefined) => { assert.ok(error); resolve(); }));

    assert.equal(fs.readFileSync(localDownload, "utf8"), "real SFTP protocol works");
    assert.equal(fs.readFileSync(path.join(base, "servers", "server-1", "uploaded.txt"), "utf8"), "real SFTP protocol works");
  } finally {
    client.end();
    await new Promise<void>(resolve => server.close(() => resolve()));
    await new Promise<void>(resolve => panel.close(() => resolve()));
    fs.rmSync(base, { recursive: true, force: true });
  }
});
